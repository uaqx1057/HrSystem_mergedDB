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
        "echo '--- find all php8.4 pool configs ---'",
        "find /etc/php/8.4 -iname '*.conf' 2>&1",
        "echo '--- grep open_basedir anywhere under /etc/php/8.4 ---'",
        "grep -rn 'open_basedir' /etc/php/8.4/ 2>&1",
        "echo '--- .user.ini in HR release root ---'",
        "cat '$release/.user.ini' 2>&1",
        "echo '--- .user.ini in HR public dir ---'",
        "cat '$release/public/.user.ini' 2>&1",
        "echo '--- find any .user.ini under HR release ---'",
        "find '$release' -maxdepth 2 -iname '.user.ini' 2>&1",
        "echo '--- user_ini.filename setting in php8.4 fpm ini ---'",
        "grep -rn 'user_ini' /etc/php/8.4/fpm/php.ini 2>&1"
    ) -join "`n"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
