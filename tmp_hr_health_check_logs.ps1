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

$logFiles = glob("/srv/apps/releases/HrSystem_mergedDB/storage/logs/laravel-*.log");
sort($logFiles);
if (empty($logFiles)) {
    echo "laravel_log=missing" . PHP_EOL;
    exit(0);
}
echo "log_files_found=" . implode(",", array_map("basename", $logFiles)) . PHP_EOL;

$lines = [];
foreach ($logFiles as $lf) {
    $lines = array_merge($lines, file($lf, FILE_IGNORE_NEW_LINES));
}
echo "total_log_lines=" . count($lines) . PHP_EOL;

$errors = [];
foreach ($lines as $line) {
    if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?\.(ERROR|CRITICAL|ALERT|EMERGENCY): (.*)/', $line, $m)) {
        $errors[] = ["ts" => $m[1], "level" => $m[2], "msg" => $m[3]];
    }
}
echo "total_error_lines=" . count($errors) . PHP_EOL;

// Group by normalized message (strip numbers/ids/quoted values) to find distinct error types
$grouped = [];
foreach ($errors as $e) {
    $norm = preg_replace('/\d+/', 'N', $e["msg"]);
    $norm = preg_replace('/"[^"]*"/', '"X"', $norm);
    $norm = substr($norm, 0, 150);
    if (!isset($grouped[$norm])) {
        $grouped[$norm] = ["count" => 0, "first" => $e["ts"], "last" => $e["ts"], "sample" => substr($e["msg"], 0, 300)];
    }
    $grouped[$norm]["count"]++;
    $grouped[$norm]["last"] = $e["ts"];
}

uasort($grouped, fn($a, $b) => $b["count"] <=> $a["count"]);

echo PHP_EOL . "=== DISTINCT ERROR TYPES (top 40 by frequency) ===" . PHP_EOL;
$i = 0;
foreach ($grouped as $norm => $data) {
    $i++;
    if ($i > 40) break;
    echo "[{$data['count']}x] first={$data['first']} last={$data['last']}" . PHP_EOL;
    echo "    " . $data["sample"] . PHP_EOL;
}
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))

$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    $cmd = "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_log_triage.php && cd '$release' && sudo -u www-data php /tmp/hr_log_triage.php; rm -f /tmp/hr_log_triage.php"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
