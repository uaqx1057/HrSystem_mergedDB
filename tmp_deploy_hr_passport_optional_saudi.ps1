$ErrorActionPreference = 'Stop'
Import-Module Posh-SSH

Set-Location $PSScriptRoot

$server = '130.94.59.87'
$user = 'root'
$passwordPlain = 'FussionHost@68373898'
$password = ConvertTo-SecureString $passwordPlain -AsPlainText -Force
$credential = New-Object System.Management.Automation.PSCredential($user, $password)
$release = '/srv/apps/releases/HrSystem_mergedDB'
$backupDir = "$release/deploy-backups/$(Get-Date -Format 'yyyyMMdd_HHmmss')_passport_optional_saudi_asterisk"
$stageRoot = "/tmp/hr-passport-optional-$(Get-Date -Format 'yyyyMMdd_HHmmss')"

$deployFiles = @(
    'resources/views/employees/ajax/create.blade.php',
    'resources/views/employees/ajax/edit.blade.php'
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
    Invoke-RemoteCommand -Command "mkdir -p '$backupDir/resources/views/employees/ajax'; cp '$releasePath' '$backupPath'" | Out-Null
}

Write-Host "Staging files under $stageRoot ..."
$sftp = New-SFTPSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    foreach ($relativePath in $deployFiles) {
        $localPath = Join-Path $PSScriptRoot ($relativePath -replace '/', '\')
        Invoke-RemoteCommand -Command "mkdir -p '$stageRoot/resources/views/employees/ajax'" | Out-Null
        Set-SFTPItem -SessionId $sftp.SessionId -Path $localPath -Destination "$stageRoot/resources/views/employees/ajax" -Force
        Write-Host "Staged: $relativePath"
    }
}
finally {
    Remove-SFTPSession -SFTPSession $sftp | Out-Null
}

try {
    Write-Host 'Promoting files...'
    foreach ($relativePath in $deployFiles) {
        $stagePath = "$stageRoot/$relativePath"
        $releasePath = "$release/$relativePath"
        Invoke-RemoteCommand -Command "cp '$stagePath' '$releasePath' && chown www-data:www-data '$releasePath' && chmod 644 '$releasePath'" | Out-Null
        Write-Host "Promoted: $relativePath"
    }

    Write-Host 'Syntax-checking...'
    foreach ($relativePath in $deployFiles) {
        Invoke-RemoteCommand -Command "php -l '$release/$relativePath'" | Out-Null
    }

    Write-Host 'Clearing view cache...'
    Invoke-RemoteCommand -Command "cd '$release'; sudo -u www-data php artisan view:clear; sudo -u www-data php artisan view:cache" | Out-Null

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
}
catch {
    Write-Host 'Deployment failed, restoring backups...'
    foreach ($relativePath in $deployFiles) {
        try {
            Invoke-RemoteCommand -Command "cp '$backupDir/$relativePath' '$release/$relativePath' && chown www-data:www-data '$release/$relativePath'" -AllowFailure | Out-Null
        }
        catch {
        }
    }
    Invoke-RemoteCommand -Command "cd '$release'; sudo -u www-data php artisan view:clear" -AllowFailure | Out-Null
    throw
}
finally {
    Invoke-RemoteCommand -Command "rm -rf '$stageRoot'" -AllowFailure | Out-Null
}

Write-Host 'PASSPORT OPTIONAL FOR SAUDI (ASTERISK FIX) DEPLOY COMPLETE'
Write-Host "Backup retained at: $backupDir"
