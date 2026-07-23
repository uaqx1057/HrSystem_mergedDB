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

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "users_has_user_id=" . (Schema::hasColumn("users", "user_id") ? "yes" : "no") . PHP_EOL;
echo "task_users_has_user_id=" . (Schema::hasColumn("task_users", "user_id") ? "yes" : "no") . PHP_EOL;
echo "task_users_columns=" . implode(",", Schema::getColumnListing("task_users")) . PHP_EOL;

// Try to reproduce the exact failing query
try {
    $completedColId = DB::table("taskboard_columns")->where("is_default_complete", 1)->value("id")
        ?? DB::table("taskboard_columns")->orderByDesc("id")->value("id");
    $sql = \App\Models\Task::with("boardColumn")
        ->where("board_column_id", "<>", $completedColId ?? 0)
        ->whereHas("users", function ($q) {
            $q->where("user_id", 1);
        })
        ->toSql();
    echo "reproduced_sql=" . $sql . PHP_EOL;
} catch (\Throwable $e) {
    echo "reproduce_error=" . $e->getMessage() . PHP_EOL;
}
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))

$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    $cmd = "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_schema_check.php && cd '$release' && sudo -u www-data php /tmp/hr_schema_check.php; rm -f /tmp/hr_schema_check.php"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
