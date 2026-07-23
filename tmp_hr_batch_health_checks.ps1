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
$a = require "bootstrap/app.php";
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo "failed_jobs_count=" . Illuminate\Support\Facades\DB::table("failed_jobs")->count() . PHP_EOL;
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))

$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    $cmd = @(
        "echo === .user.ini files anywhere in release ===",
        "find '$release' -iname '.user.ini' -exec echo {} \; -exec cat {} \;",
        "echo",
        "echo === migrate:status last 25 lines ===",
        "cd '$release' && sudo -u www-data php artisan migrate:status 2>&1 | tail -25",
        "echo",
        "echo === composer.lock vs vendor drift check ===",
        "grep -A2 symfony/css-selector '$release/composer.json' 2>&1 | head -5",
        "grep -c symfony/css-selector '$release/composer.lock' 2>&1",
        "grep -A2 saloonphp/xml-wrangler '$release/composer.json' 2>&1 | head -5",
        "cat '$release/vendor/saloonphp/xml-wrangler/composer.json' 2>/dev/null | grep version",
        "grep -A3 saloonphp/xml-wrangler '$release/composer.lock' 2>&1 | head -6",
        "echo",
        "echo === supervisor queue worker status ===",
        "supervisorctl status 2>&1 | grep -i hr",
        "echo",
        "echo === failed_jobs count ===",
        "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_failedjobs_probe.php && cd '$release' && sudo -u www-data php /tmp/hr_failedjobs_probe.php; rm -f /tmp/hr_failedjobs_probe.php",
        "echo",
        "echo === disk space ===",
        "df -h / 2>&1",
        "echo",
        "echo === crontab for laravel scheduler ===",
        "crontab -l 2>&1 | grep -i hr",
        "echo",
        "echo === config services.php live sso check ===",
        "grep -n sso '$release/config/services.php' 2>&1 | head -10"
    ) -join "`n"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
