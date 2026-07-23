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
use App\Models\Team;
use App\Models\Designation;
use App\Models\Branch;
use App\Models\Role;
use App\Enums\Salutation;
use App\Models\EmployeeDetails;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeDependant;
use App\Models\LanguageSetting;
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\Auth;

function out(string $key, $value): void {
    if (is_bool($value)) { $value = $value ? "yes" : "no"; }
    echo $key . "=" . (is_scalar($value) || $value === null ? (string) $value : json_encode($value)) . PHP_EOL;
}
function fail(string $msg): void { fwrite(STDERR, $msg . PHP_EOL); exit(1); }

$probeUser = User::withoutGlobalScopes()->where("role_id", 1)->orderBy("id")->first();
$probeAuth = UserAuth::find($probeUser->user_auth_id);
Auth::guard()->setUser($probeAuth);
session(["company" => (object) [
    "id" => $probeUser->company_id,
    "timezone" => "Asia/Riyadh",
    "date_format" => "d-m-Y",
    "time_format" => "h:i A",
    "currency_symbol" => "SAR",
    "currency_symbol_position" => "before",
    "currency" => "SAR",
]]);

$employee = User::withoutGlobalScope(ActiveScope::class)->with("employeeDetail", "reportingTeam")->find($probeUser->id);
if (!$employee || !$employee->employeeDetail) {
    $employee = User::withoutGlobalScope(ActiveScope::class)->with("employeeDetail", "reportingTeam")->whereHas("employeeDetail")->first();
}
if (!$employee) { fail("No employee with employeeDetail found."); }
out("target_employee_id", $employee->id);
out("target_employee_type_before", $employee->employeeDetail->employee_type);
$employee->open_tasks_count = 0;
$employee->member_count = 0;
$employee->agents_count = 0;
$employee->reportingTeam = collect();

$commonData = [
    "teams" => Team::all(),
    "designations" => Designation::allDesignations(),
    "branches" => Branch::get(),
    "skills" => [],
    "countries" => countries(),
    "employees" => User::allEmployees(null, true),
    "languages" => LanguageSetting::where("status", "enabled")->get(),
    "salutations" => Salutation::cases(),
    "roles" => Role::where("name", "<>", "client")->get(),
    "fields" => collect(),
    "addDesignationPermission" => false,
];

try {
    $createData = $commonData + [
        "lastEmployeeID" => EmployeeDetails::count(),
        "checkifExistEmployeeId" => null,
    ];
    $html = view("employees.ajax.create", $createData)->render();
    out("create_render_len", strlen($html));
    out("create_has_employee_type", str_contains($html, 'id="employee_type"'));
    out("create_has_national_id", str_contains($html, 'id="national_id"'));
} catch (\Throwable $e) {
    fail("CREATE VIEW ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
}

try {
    $existingDependants = EmployeeDependant::where("employee_id", $employee->id)->get();
    $rawMaritalStatus = $employee->employeeDetail->marital_status;
    $storedMaritalStatus = ($rawMaritalStatus instanceof \App\Enums\MaritalStatus) ? $rawMaritalStatus->value : (string) $rawMaritalStatus;
    $isMarried = $storedMaritalStatus === \App\Enums\MaritalStatus::Married->value;

    $editData = $commonData + [
        "employee" => $employee,
        "employeeDetail" => $employee->employeeDetail->withCustomFields(),
        "changeEmployeeRolePermission" => false,
        "existingAllowances" => EmployeeAllowance::where("employee_id", $employee->id)->get(),
        "userRoles" => [],
        "emailCountInCompanies" => 1,
    ];
    $html = view("employees.ajax.edit", $editData)->render();
    out("edit_render_len", strlen($html));
    out("edit_has_employee_type", str_contains($html, 'id="employee_type"'));
    out("edit_has_national_id", str_contains($html, 'id="national_id"'));
    out("edit_employee_type_selected_expat", str_contains($html, 'value="expat" selected') || str_contains($html, 'selected>Expat') || preg_match('/value="expat"[^>]*selected/', $html) === 1);
} catch (\Throwable $e) {
    fail("EDIT VIEW ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
}

try {
    $employeeDetail = $employee->employeeDetail;
    $allowance = EmployeeAllowance::where("employee_id", $employee->id)->get();
    $dependants = EmployeeDependant::where("employee_id", $employee->id)->get();
    $showFullProfile = true;
    $isMarried = false;

    $profileData = [
        "employee" => $employee,
        "allowance" => $allowance,
        "dependants" => $dependants,
        "showFullProfile" => $showFullProfile,
        "isMarried" => $isMarried,
        "viewPermission" => "all",
        "editPermission" => "all",
        "editEmployeePermission" => "all",
        "employeeLanguage" => null,
        "employeeInsurances" => collect(),
        "leaveHistory" => [],
        "hoursLogged" => 0,
    ];
    $html = view("employees.ajax.profile", $profileData)->render();
    out("profile_render_len", strlen($html));
    out("profile_has_employee_type_label", str_contains($html, "Employee Type"));
} catch (\Throwable $e) {
    fail("PROFILE VIEW ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
}

// Toggle employee_type to saudi and re-render edit + profile to test that branch too
try {
    $employee->employeeDetail->employee_type = "saudi";
    $employee->employeeDetail->national_id = "1234567890";
    $html = view("employees.ajax.edit", $editData)->render();
    out("edit_saudi_render_len", strlen($html));
    out("edit_saudi_has_national_id_value", str_contains($html, "1234567890"));

    $html = view("employees.ajax.profile", $profileData)->render();
    out("profile_saudi_render_len", strlen($html));
    out("profile_saudi_shows_national_id_label", str_contains($html, "National ID"));
} catch (\Throwable $e) {
    fail("SAUDI-BRANCH VIEW ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
}

out("all_renders_ok", true);
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))

$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    $cmd = "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_render_probe.php && cd '$release' && sudo -u www-data php /tmp/hr_render_probe.php; rm -f /tmp/hr_render_probe.php"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
