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
        "echo '--- .env DRIVER_DOCUMENT_PATH ---'",
        "grep -n 'DRIVER_DOCUMENT_PATH' '$release/.env' || echo 'NOT SET IN .env'",
        "echo '--- shared_storage/driver_documents exists? ---'",
        "ls -la /srv/apps/shared_storage/ 2>&1 | head -20",
        "echo '--- driver_documents subfolder ---'",
        "ls -la /srv/apps/shared_storage/driver_documents/ 2>&1 | head -10",
        "echo '--- look for 571 folder / target file anywhere under shared_storage ---'",
        "find /srv/apps/shared_storage -iname '20260720122810_other.pdf' 2>&1",
        "find /srv/apps/shared_storage -maxdepth 3 -type d -name '571' 2>&1",
        "echo '--- php8.4-fpm hr pool open_basedir ---'",
        "grep -rn 'open_basedir' /etc/php/8.4/fpm/pool.d/ 2>&1",
        "echo '--- php8.2-fpm www pool open_basedir (for comparison) ---'",
        "grep -rn 'open_basedir' /etc/php/8.2/fpm/pool.d/ 2>&1"
    ) -join "`n"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
