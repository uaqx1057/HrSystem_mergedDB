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
        "echo === paystack.php ===",
        "cat '$release/config/paystack.php' 2>&1",
        "echo === payfast.php full ===",
        "cat '$release/config/payfast.php' 2>&1",
        "echo === flutterwave.php ===",
        "cat '$release/config/flutterwave.php' 2>&1",
        "echo === mollie.php key line ===",
        "grep -n key '$release/config/mollie.php' 2>&1",
        "echo === cashier.php key lines ===",
        "grep -n STRIPE_KEY '$release/config/cashier.php' 2>&1",
        "grep -n STRIPE_SECRET '$release/config/cashier.php' 2>&1",
        "grep -n webhook '$release/config/cashier.php' 2>&1"
    ) -join "`n"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
