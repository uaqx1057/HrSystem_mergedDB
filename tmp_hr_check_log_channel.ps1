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
        "echo '--- LOG_CHANNEL in .env ---'",
        "grep -n '^LOG_' '$release/.env'",
        "echo '--- storage/logs directory listing ---'",
        "ls -la '$release/storage/logs/' 2>&1",
        "echo '--- storage/logs permissions ---'",
        "stat '$release/storage/logs' 2>&1 | head -5",
        "echo '--- can www-data write there? ---'",
        "sudo -u www-data touch '$release/storage/logs/write_test.tmp' 2>&1 && echo WRITE_OK && rm -f '$release/storage/logs/write_test.tmp' || echo WRITE_FAILED"
    ) -join "`n"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
