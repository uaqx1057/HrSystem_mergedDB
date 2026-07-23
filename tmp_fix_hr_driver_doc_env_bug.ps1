$ErrorActionPreference = 'Stop'
Import-Module Posh-SSH

Set-Location $PSScriptRoot

$server = '130.94.59.87'
$user = 'root'
$passwordPlain = 'FussionHost@68373898'
$password = ConvertTo-SecureString $passwordPlain -AsPlainText -Force
$credential = New-Object System.Management.Automation.PSCredential($user, $password)
$release = '/srv/apps/releases/HrSystem_mergedDB'
$backupDir = "$release/deploy-backups/$(Get-Date -Format 'yyyyMMdd_HHmmss')_driver_doc_env_config_fix"

$deployFiles = @(
    'app/Http/Controllers/DriverDocumentController.php',
    'app/Console/Commands/MigrateDriverDocuments.php'
)

function Invoke-RemoteCommand {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Command,
        [switch] $AllowFailure
    )

    $session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
    try {
        $normalizedCommand = $Command -replace "`r", ''
        $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $normalizedCommand

        if ($result.Output) {
            $result.Output | ForEach-Object { Write-Host $_ }
        }

        if ($result.Error) {
            $result.Error | ForEach-Object { Write-Host $_ }
        }

        if (-not $AllowFailure -and $result.ExitStatus -ne 0) {
            throw "Remote command failed with exit code $($result.ExitStatus): $Command"
        }

        return $result
    }
    finally {
        Remove-SSHSession -SSHSession $session | Out-Null
    }
}

Write-Host "Backing up files to $backupDir ..."
foreach ($relativePath in $deployFiles) {
    $releasePath = "$release/$relativePath"
    $backupPath = "$backupDir/$relativePath"
    $backupDirectory = Split-Path ($backupDir + '/' + $relativePath) -Parent
    Invoke-RemoteCommand -Command "mkdir -p '$($backupDirectory -replace '\\','/')'; cp '$releasePath' '$backupPath'" | Out-Null
}

Write-Host "Uploading fixed files..."
$sftp = New-SFTPSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    foreach ($relativePath in $deployFiles) {
        $localPath = Join-Path $PSScriptRoot ($relativePath -replace '/', '\')
        $releaseDir = "$release/" + ($relativePath -replace '/[^/]+$', '')
        Set-SFTPItem -SessionId $sftp.SessionId -Path $localPath -Destination '/tmp' -Force
        $fileName = Split-Path $localPath -Leaf
        Invoke-RemoteCommand -Command "cp '/tmp/$fileName' '$release/$relativePath' && chown www-data:www-data '$release/$relativePath' && chmod 644 '$release/$relativePath' && rm -f '/tmp/$fileName'" | Out-Null
        Write-Host "Deployed: $relativePath"
    }
}
finally {
    Remove-SFTPSession -SFTPSession $sftp | Out-Null
}

Write-Host 'Syntax-checking...'
foreach ($relativePath in $deployFiles) {
    Invoke-RemoteCommand -Command "php -l '$release/$relativePath'" | Out-Null
}

Write-Host 'Clearing caches...'
Invoke-RemoteCommand -Command "cd '$release'; sudo -u www-data php artisan optimize:clear; sudo -u www-data php artisan config:cache" | Out-Null

Write-Host 'Reloading php8.4-fpm...'
Invoke-RemoteCommand -Command "systemctl reload php8.4-fpm; systemctl is-active php8.4-fpm" | Out-Null

Write-Host 'Verifying document 267 now resolves correctly...'
$probeScript = @'
<?php
require "/srv/apps/releases/HrSystem_mergedDB/vendor/autoload.php";
$app = require "/srv/apps/releases/HrSystem_mergedDB/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DriverDocument;
use Illuminate\Support\Facades\Storage;

function out(string $key, $value): void {
    if (is_bool($value)) { $value = $value ? "yes" : "no"; }
    echo $key . "=" . (is_scalar($value) || $value === null ? (string) $value : json_encode($value)) . PHP_EOL;
}
function fail(string $msg): void { fwrite(STDERR, $msg . PHP_EOL); exit(1); }

$document = DriverDocument::find(267);
if (!$document) { fail("DriverDocument 267 not found"); }
out("db_file_path", $document->file_path);

$fullPath = Storage::disk("driver_documents")->path($document->file_path);
out("resolved_full_path", $fullPath);

try {
    out("file_exists", file_exists($fullPath));
    if (file_exists($fullPath)) {
        out("file_size", filesize($fullPath));
        out("mime_type", mime_content_type($fullPath));
    } else {
        fail("File still not found at resolved path.");
    }
} catch (\Throwable $e) {
    fail("STILL BROKEN: " . $e->getMessage());
}
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))
Invoke-RemoteCommand -Command "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_docfix_verify.php && cd '$release' && sudo -u www-data php /tmp/hr_docfix_verify.php; rm -f /tmp/hr_docfix_verify.php" | Out-Null

Write-Host 'Checking recent production errors...'
$logCheckCommand = @"
cd '$release'
sudo -u www-data php -r '
`$path = "$release/storage/logs/laravel.log";
if (!file_exists(`$path)) {
    echo "laravel_log=missing" . PHP_EOL;
    exit(0);
}
`$lines = @file(`$path, FILE_IGNORE_NEW_LINES) ?: [];
`$recent = array_values(array_filter(array_slice(`$lines, -200), fn (`$line) => str_contains(`$line, "production.ERROR")));
echo "recent_production_error_count=" . count(`$recent) . PHP_EOL;
if (`$recent) {
    echo "recent_last_error=" . substr(end(`$recent), 0, 500) . PHP_EOL;
}
'
"@
Invoke-RemoteCommand -Command $logCheckCommand | Out-Null

Write-Host 'DRIVER DOCUMENT PATH FIX DEPLOY COMPLETE'
Write-Host "Backup retained at: $backupDir"
