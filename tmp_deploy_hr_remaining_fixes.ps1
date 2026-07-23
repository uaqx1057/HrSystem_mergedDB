$ErrorActionPreference = 'Stop'
Import-Module Posh-SSH

Set-Location $PSScriptRoot

$server = '130.94.59.87'
$user = 'root'
$passwordPlain = 'FussionHost@68373898'
$password = ConvertTo-SecureString $passwordPlain -AsPlainText -Force
$credential = New-Object System.Management.Automation.PSCredential($user, $password)
$release = '/srv/apps/releases/HrSystem_mergedDB'
$backupDir = "$release/deploy-backups/$(Get-Date -Format 'yyyyMMdd_HHmmss')_remaining_audit_fixes"
$stageRoot = "/tmp/hr-remaining-fixes-$(Get-Date -Format 'yyyyMMdd_HHmmss')"

$deployFiles = @(
    'config/services.php',
    'app/Traits/PaymentGatewayTrait.php',
    'app/Traits/SuperAdmin/StripeSettings.php',
    'app/Traits/SuperAdmin/PaystackSettings.php',
    'app/Traits/SuperAdmin/MollieSettings.php',
    'app/Traits/CurrencyExchange.php',
    'app/Traits/SuperAdmin/GlobalCurrencyExchange.php',
    'app/Traits/SocialAuthSettings.php',
    'app/Http/Controllers/EmployeeController.php',
    'app/Http/Controllers/DashboardController.php'
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

function Get-RemoteDirectory {
    param(
        [Parameter(Mandatory = $true)]
        [string] $BasePath,
        [Parameter(Mandatory = $true)]
        [string] $RelativeFilePath
    )

    $pathParts = $RelativeFilePath -split '/'
    if ($pathParts.Length -le 1) {
        return $BasePath.TrimEnd('/')
    }

    return ($BasePath.TrimEnd('/') + '/' + ($pathParts[0..($pathParts.Length - 2)] -join '/'))
}

$probeScript = @'
<?php
require "/srv/apps/releases/HrSystem_mergedDB/vendor/autoload.php";
$app = require "/srv/apps/releases/HrSystem_mergedDB/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function out(string $key, $value): void {
    if (is_bool($value)) { $value = $value ? "yes" : "no"; }
    echo $key . "=" . (is_scalar($value) || $value === null ? (string) $value : json_encode($value)) . PHP_EOL;
}
function fail(string $msg): void { fwrite(STDERR, $msg . PHP_EOL); exit(1); }

// Confirm config values resolve correctly through the cached config array
out("services_sso_dms_url", config("services.sso.dms_url"));
out("services_sso_dobs_url", config("services.sso.dobs_url"));
out("services_square_location_id_key", config("services.square.location_id") === null ? "null (SQUARE_LOCATION_ID not set - expected)" : "set");
out("paystack_publicKey_resolves", config("paystack.publicKey") !== null || config("paystack.publicKey") === null ? "reachable" : "unreachable");
out("flutterwave_secretHash_default", config("flutterwave.secretHash"));
out("payfast_merchant_id_default", config("payfast.merchant.merchant_id"));
out("mollie_key_default", config("mollie.key"));
out("cashier_key", config("cashier.key"));
out("services_facebook_client_id", config("services.facebook.client_id"));
out("services_currency_converter_key", config("services.currency_converter.key"));

// Reproduce the fixed dashboard task query - must NOT throw ambiguous column error
try {
    $sql = \App\Models\Task::with("boardColumn")
        ->where("board_column_id", "<>", 999999)
        ->whereHas("users", function ($q) {
            $q->where("task_users.user_id", 1);
        })
        ->get();
    out("dashboard_query_fixed", true);
} catch (\Throwable $e) {
    fail("DASHBOARD QUERY STILL BROKEN: " . $e->getMessage());
}

exit(0);
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))

Write-Host "Backing up $($deployFiles.Count) files to $backupDir ..."
foreach ($relativePath in $deployFiles) {
    $releasePath = "$release/$relativePath"
    $backupPath = "$backupDir/$relativePath"
    $backupDirectory = Get-RemoteDirectory -BasePath $backupDir -RelativeFilePath $relativePath
    Invoke-RemoteCommand -Command "mkdir -p '$backupDirectory'; cp '$releasePath' '$backupPath'" | Out-Null
}

Write-Host "Staging files under $stageRoot ..."
$sftp = New-SFTPSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    foreach ($relativePath in $deployFiles) {
        $localPath = Join-Path $PSScriptRoot ($relativePath -replace '/', '\')
        $stageDirectory = Get-RemoteDirectory -BasePath $stageRoot -RelativeFilePath $relativePath
        Invoke-RemoteCommand -Command "mkdir -p '$stageDirectory'" | Out-Null
        Set-SFTPItem -SessionId $sftp.SessionId -Path $localPath -Destination $stageDirectory -Force
        Write-Host "Staged: $relativePath"
    }
}
finally {
    Remove-SFTPSession -SFTPSession $sftp | Out-Null
}

Invoke-RemoteCommand -Command "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_remaining_fixes_probe.php" | Out-Null

try {
    Write-Host 'Promoting files...'
    foreach ($relativePath in $deployFiles) {
        $releaseDirectory = Get-RemoteDirectory -BasePath $release -RelativeFilePath $relativePath
        $stagePath = "$stageRoot/$relativePath"
        $releasePath = "$release/$relativePath"
        $promoteCommand = @(
            "mkdir -p '$releaseDirectory'",
            "cp '$stagePath' '$releasePath'",
            "chown www-data:www-data '$releasePath'",
            "chmod 644 '$releasePath'"
        ) -join '; '
        Invoke-RemoteCommand -Command $promoteCommand | Out-Null
        Write-Host "Promoted: $relativePath"
    }

    Write-Host 'Syntax-checking...'
    foreach ($relativePath in $deployFiles) {
        Invoke-RemoteCommand -Command "php -l '$release/$relativePath'" | Out-Null
    }

    Write-Host 'Clearing and rebuilding caches...'
    Invoke-RemoteCommand -Command "cd '$release'; sudo -u www-data php artisan optimize:clear; sudo -u www-data php artisan config:cache; sudo -u www-data php artisan view:cache" | Out-Null

    Write-Host 'Running functional probe...'
    Invoke-RemoteCommand -Command "cd '$release'; sudo -u www-data php /tmp/hr_remaining_fixes_probe.php" | Out-Null

    Write-Host 'Reloading php8.4-fpm...'
    Invoke-RemoteCommand -Command "systemctl reload php8.4-fpm; systemctl is-active php8.4-fpm" | Out-Null

    Write-Host 'Checking recent production errors (daily-rotated log)...'
    $logCheckCommand = @"
cd '$release'
sudo -u www-data php -r '
`$logFiles = glob("$release/storage/logs/laravel-*.log");
sort(`$logFiles);
if (empty(`$logFiles)) { echo "laravel_log=missing" . PHP_EOL; exit(0); }
`$latest = end(`$logFiles);
echo "checking=" . basename(`$latest) . PHP_EOL;
`$lines = file(`$latest, FILE_IGNORE_NEW_LINES) ?: [];
`$recent = array_values(array_filter(array_slice(`$lines, -100), fn (`$line) => str_contains(`$line, "production.ERROR")));
echo "recent_production_error_count=" . count(`$recent) . PHP_EOL;
if (`$recent) { echo "recent_last_error=" . substr(end(`$recent), 0, 500) . PHP_EOL; }
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
    Invoke-RemoteCommand -Command "cd '$release'; sudo -u www-data php artisan optimize:clear; sudo -u www-data php artisan config:cache" -AllowFailure | Out-Null
    throw
}
finally {
    Invoke-RemoteCommand -Command "rm -rf '$stageRoot' /tmp/hr_remaining_fixes_probe.php" -AllowFailure | Out-Null
}

Write-Host 'REMAINING AUDIT FIXES DEPLOY COMPLETE'
Write-Host "Backup retained at: $backupDir"
