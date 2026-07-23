$ErrorActionPreference = 'Stop'
Import-Module Posh-SSH

$server = '130.94.59.87'
$user = 'root'
$passwordPlain = 'FussionHost@68373898'
$password = ConvertTo-SecureString $passwordPlain -AsPlainText -Force
$credential = New-Object System.Management.Automation.PSCredential($user, $password)

$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    $cmd = @(
        "echo === /etc/crontab ===",
        "cat /etc/crontab 2>&1",
        "echo",
        "echo === /etc/cron.d/ ===",
        "ls -la /etc/cron.d/ 2>&1",
        'for f in /etc/cron.d/*; do echo "--- $f ---"; cat "$f" 2>&1; done',
        "echo",
        "echo === root crontab full ===",
        "crontab -l 2>&1",
        "echo",
        "echo === all supervisor programs (full list, not just hr) ===",
        "supervisorctl status 2>&1",
        "echo",
        "echo === supervisor configs mentioning schedule ===",
        "grep -rl schedule /etc/supervisor/ 2>&1",
        "grep -rn schedule /etc/supervisor/conf.d/*.conf 2>&1",
        "echo",
        "echo === any process currently running artisan schedule ===",
        "ps aux | grep -i schedule | grep -v grep"
    ) -join "`n"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
