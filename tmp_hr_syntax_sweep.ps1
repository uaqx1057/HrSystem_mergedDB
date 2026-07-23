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
    $cmd = "find '$release/app' -name '*.php' -print0 | xargs -0 -n1 -P4 php -l 2>&1 | grep -v 'No syntax errors detected'"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    if ($result.Output) {
        Write-Host "=== Files with syntax problems (excluding clean files) ==="
        $result.Output | ForEach-Object { Write-Host $_ }
    } else {
        Write-Host "No output - all files clean, or command produced nothing."
    }
    $result.Error | ForEach-Object { Write-Host $_ }
    Write-Host "Exit status: $($result.ExitStatus)"
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
