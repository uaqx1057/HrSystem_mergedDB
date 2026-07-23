$ErrorActionPreference = 'Stop'
Import-Module Posh-SSH

Set-Location $PSScriptRoot

$server = '130.94.59.87'
$user = 'root'
$passwordPlain = 'FussionHost@68373898'
$password = ConvertTo-SecureString $passwordPlain -AsPlainText -Force
$credential = New-Object System.Management.Automation.PSCredential($user, $password)
$release = '/srv/apps/releases/HrSystem_mergedDB'
$backupDir = "$release/deploy-backups/$(Get-Date -Format 'yyyyMMdd_HHmmss')_user_ini_open_basedir_fix"
$relativePath = 'public/.user.ini'
$localPath = Join-Path $PSScriptRoot ($relativePath -replace '/', '\')

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

Write-Host "Backing up current .user.ini to $backupDir ..."
Invoke-RemoteCommand -Command "mkdir -p '$backupDir/public'; cp '$release/$relativePath' '$backupDir/$relativePath'" | Out-Null

Write-Host "Uploading corrected .user.ini ..."
$sftp = New-SFTPSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    Set-SFTPItem -SessionId $sftp.SessionId -Path $localPath -Destination '/tmp' -Force
}
finally {
    Remove-SFTPSession -SFTPSession $sftp | Out-Null
}

Invoke-RemoteCommand -Command "cp /tmp/.user.ini '$release/$relativePath' && chown www-data:www-data '$release/$relativePath' && chmod 644 '$release/$relativePath' && rm -f /tmp/.user.ini" | Out-Null

Write-Host "New .user.ini content:"
Invoke-RemoteCommand -Command "cat '$release/$relativePath'" | Out-Null

Write-Host "Reloading php8.4-fpm so the .user.ini change takes effect immediately..."
Invoke-RemoteCommand -Command "systemctl reload php8.4-fpm; systemctl is-active php8.4-fpm" | Out-Null

Write-Host "Verifying the previously-broken document now loads..."
$probeScript = @'
<?php
require "/srv/apps/releases/HrSystem_mergedDB/vendor/autoload.php";
$app = require "/srv/apps/releases/HrSystem_mergedDB/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DriverDocument;

function out(string $key, $value): void {
    if (is_bool($value)) { $value = $value ? "yes" : "no"; }
    echo $key . "=" . (is_scalar($value) || $value === null ? (string) $value : json_encode($value)) . PHP_EOL;
}
function fail(string $msg): void { fwrite(STDERR, $msg . PHP_EOL); exit(1); }

$document = DriverDocument::find(267);
if (!$document) { fail("DriverDocument 267 not found"); }

$fullPath = env("DRIVER_DOCUMENT_PATH") . "/" . $document->file_path;
out("full_path", $fullPath);

try {
    out("file_exists", file_exists($fullPath));
    if (file_exists($fullPath)) {
        out("file_size", filesize($fullPath));
        out("mime_type", mime_content_type($fullPath));
    }
} catch (\Throwable $e) {
    fail("STILL BROKEN: " . $e->getMessage());
}
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))
Invoke-RemoteCommand -Command "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_docpath_probe.php && cd '$release' && sudo -u www-data php /tmp/hr_docpath_probe.php; rm -f /tmp/hr_docpath_probe.php" | Out-Null

Write-Host 'USER.INI OPEN_BASEDIR FIX COMPLETE'
Write-Host "Backup retained at: $backupDir/$relativePath"
