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

use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

function out(string $key, $value): void {
    if (is_bool($value)) { $value = $value ? "yes" : "no"; }
    echo $key . "=" . (is_scalar($value) || $value === null ? (string) $value : json_encode($value)) . PHP_EOL;
}

$permittedUserId = \Illuminate\Support\Facades\DB::table("user_permissions")
    ->join("permissions", "user_permissions.permission_id", "=", "permissions.id")
    ->join("permission_types", "user_permissions.permission_type_id", "=", "permission_types.id")
    ->where("permissions.name", "add_employees")
    ->whereIn("permission_types.name", ["all", "added"])
    ->value("user_permissions.user_id");

$probeUser = ($permittedUserId ? User::withoutGlobalScopes()->find($permittedUserId) : null)
    ?: User::withoutGlobalScopes()->where("role_id", 1)->orderBy("id")->first();
$probeAuth = UserAuth::find($probeUser->user_auth_id);
Auth::guard()->setUser($probeAuth);
out("probe_user_id", $probeUser->id);

$employeeWithDetail = User::query()->whereHas("employeeDetail")->with("employeeDetail")->first();

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$targets = ["account/employees/create"];
if ($employeeWithDetail) {
    out("probe_employee_id", $employeeWithDetail->id);
    $targets[] = "account/employees/" . $employeeWithDetail->id . "/edit";
    $targets[] = "account/employees/" . $employeeWithDetail->id;
}

foreach ($targets as $uri) {
    $request = Request::create("/" . $uri, "GET");
    $request->server->set("HTTP_HOST", "hr.speedlogi.sa");
    $request->headers->set("X-Requested-With", "XMLHttpRequest");
    Auth::guard()->setUser($probeAuth);
    $request->setUserResolver(fn() => $probeAuth);
    $response = $httpKernel->handle($request);
    $status = $response->getStatusCode();
    $content = (string) $response->getContent();
    $httpKernel->terminate($request, $response);
    $key = trim((string) preg_replace("/[^a-z0-9]+/i", "_", trim($uri, "/")), "_");
    out("route_status_" . $key, $status);
    out("route_length_" . $key, strlen($content));
    out("has_employee_type_" . $key, str_contains($content, "employee_type"));
    if ($status >= 500) {
        fwrite(STDERR, "500 for " . $uri . ": " . substr($content, 0, 1000) . PHP_EOL);
        exit(1);
    }
}
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))

$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    $cmd = "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_route_probe2.php && cd '$release' && sudo -u www-data php /tmp/hr_route_probe2.php; rm -f /tmp/hr_route_probe2.php"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
