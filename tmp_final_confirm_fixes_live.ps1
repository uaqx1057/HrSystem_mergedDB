$ErrorActionPreference = 'Stop'
Import-Module Posh-SSH

$server = '130.94.59.87'
$user = 'root'
$passwordPlain = 'FussionHost@68373898'
$password = ConvertTo-SecureString $passwordPlain -AsPlainText -Force
$credential = New-Object System.Management.Automation.PSCredential($user, $password)
$release = '/srv/apps/releases/HrSystem_mergedDB'

$probeScript = @'
<?php
require "vendor/autoload.php";
$f = glob("storage/logs/laravel-*.log");
sort($f);
$latest = end($f);
echo "checking_log=" . basename($latest) . PHP_EOL;
$lines = file($latest, FILE_IGNORE_NEW_LINES) ?: [];
$errs = array_values(array_filter($lines, fn($l) => str_contains($l, "production.ERROR")));
echo "error_count=" . count($errs) . PHP_EOL;
foreach (array_slice($errs, -5) as $e) {
    echo substr($e, 0, 150) . PHP_EOL;
}
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))

$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    $cmd = @(
        "echo === DashboardController.php has the qualified fix ===",
        "grep -n task_users.user_id '$release/app/Http/Controllers/DashboardController.php' 2>&1",
        "echo",
        "echo === DriverDocumentController.php uses Storage disk not raw env ===",
        "grep -n Storage::disk '$release/app/Http/Controllers/DriverDocumentController.php' 2>&1",
        "grep -c DRIVER_DOCUMENT_PATH '$release/app/Http/Controllers/DriverDocumentController.php' 2>&1",
        "echo",
        "echo === public/.user.ini current content ===",
        "cat '$release/public/.user.ini' 2>&1",
        "echo",
        "echo === config/services.php has sso + paystack + facebook blocks ===",
        'grep -n -E "sso|paystack|facebook" ' + "'$release/config/services.php' 2>&1",
        "echo",
        "echo === today log error count (post all fixes) ===",
        "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_final_confirm_probe.php && cd '$release' && sudo -u www-data php /tmp/hr_final_confirm_probe.php; rm -f /tmp/hr_final_confirm_probe.php"
    ) -join "`n"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd -TimeOut 90
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
