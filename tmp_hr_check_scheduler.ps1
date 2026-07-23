$ErrorActionPreference = 'Stop'
Import-Module Posh-SSH

$server = '130.94.59.87'
$user = 'root'
$passwordPlain = 'FussionHost@68373898'
$password = ConvertTo-SecureString $passwordPlain -AsPlainText -Force
$credential = New-Object System.Management.Automation.PSCredential($user, $password)
$release = '/srv/apps/releases/HrSystem_mergedDB'

$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    $cmd = @(
        "echo === www-data crontab ===",
        "crontab -u www-data -l 2>&1",
        "echo",
        "echo === all crontabs on system mentioning artisan or schedule ===",
        "grep -rl schedule:run /var/spool/cron/ 2>&1",
'for f in /var/spool/cron/crontabs/*; do echo "--- $f ---"; cat "$f" 2>&1; done',
        "echo",
        "echo === systemd timers mentioning laravel or hr ===",
        "systemctl list-timers --all 2>&1 | grep -i -E 'hr|laravel|schedule'",
        "echo",
        "echo === Kernel.php scheduled commands ===",
        "grep -n 'Schedule::' '$release/app/Console/Kernel.php' 2>&1 | head -20"
    ) -join "`n"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
