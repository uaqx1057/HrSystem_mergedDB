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
require "/srv/apps/releases/HrSystem_mergedDB/vendor/autoload.php";
$app = require "/srv/apps/releases/HrSystem_mergedDB/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Reproduce the EXACT broken query (unqualified) and run it for real to confirm the SQL error
try {
    $tasks = \App\Models\Task::with("boardColumn")
        ->where("board_column_id", "<>", 999999)
        ->whereHas("users", function ($q) {
            $q->where("user_id", 1);
        })
        ->get();
    echo "unqualified_ran_ok=yes (unexpected)" . PHP_EOL;
} catch (\Throwable $e) {
    echo "unqualified_error=" . $e->getMessage() . PHP_EOL;
}

// Now the proposed fix: qualify with task_users.user_id
try {
    $tasks = \App\Models\Task::with("boardColumn")
        ->where("board_column_id", "<>", 999999)
        ->whereHas("users", function ($q) {
            $q->where("task_users.user_id", 1);
        })
        ->get();
    echo "qualified_ran_ok=yes" . PHP_EOL;
    echo "qualified_result_count=" . $tasks->count() . PHP_EOL;
} catch (\Throwable $e) {
    echo "qualified_error=" . $e->getMessage() . PHP_EOL;
}
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))

$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    $cmd = "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_task_query_verify.php && cd '$release' && sudo -u www-data php /tmp/hr_task_query_verify.php; rm -f /tmp/hr_task_query_verify.php"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
