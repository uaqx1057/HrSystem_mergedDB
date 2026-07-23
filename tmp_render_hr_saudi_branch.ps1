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
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\Blade;

function out(string $key, $value): void {
    if (is_bool($value)) { $value = $value ? "yes" : "no"; }
    echo $key . "=" . (is_scalar($value) || $value === null ? (string) $value : json_encode($value)) . PHP_EOL;
}
function fail(string $msg): void { fwrite(STDERR, $msg . PHP_EOL); exit(1); }

$employee = User::withoutGlobalScope(ActiveScope::class)->with("employeeDetail")->whereHas("employeeDetail")->first();
if (!$employee) { fail("no employee found"); }

// Isolated copy of exactly the Saudi/Expat block added to profile.blade.php,
// extracted so it can be tested without the surrounding page needing full stub data.
$snippet = <<<'BLADE'
@if (($employee->employeeDetail->employee_type ?? 'expat') === 'saudi')
    NATIONAL_ID_BLOCK|{{ $employee->employeeDetail->national_id ?? '--' }}|{{ $employee->employeeDetail->national_id_expiry_date ? \Carbon\Carbon::parse($employee->employeeDetail->national_id_expiry_date)->translatedFormat('d-m-Y') : '--' }}
    @if ($employee->employeeDetail->national_id_image)
        HAS_NATIONAL_ID_IMAGE
    @endif
@else
    IQAMA_BLOCK|{{ $employee->employeeDetail->iqama_no ?? '--' }}
@endif
@if (($employee->employeeDetail->employee_type ?? 'expat') !== 'saudi')
    SPONSOR_BLOCK_SHOWN
@else
    SPONSOR_BLOCK_HIDDEN
@endif
BLADE;

foreach (['expat', 'saudi'] as $type) {
    $employee->employeeDetail->employee_type = $type;
    $employee->employeeDetail->national_id = '1098765432';
    $employee->employeeDetail->national_id_expiry_date = '2030-01-15';
    try {
        $rendered = Blade::render($snippet, ['employee' => $employee]);
        out($type . '_render_ok', true);
        out($type . '_output', trim(preg_replace('/\s+/', ' ', $rendered)));
    } catch (\Throwable $e) {
        fail($type . ' branch error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    }
}

out('all_branches_ok', true);
'@
$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))

$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    $cmd = "printf '%s' '$probeBase64' | base64 -d > /tmp/hr_saudi_branch_probe.php && cd '$release' && sudo -u www-data php /tmp/hr_saudi_branch_probe.php; rm -f /tmp/hr_saudi_branch_probe.php"
    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $cmd
    $result.Output | ForEach-Object { Write-Host $_ }
    $result.Error | ForEach-Object { Write-Host $_ }
}
finally {
    Remove-SSHSession -SSHSession $session | Out-Null
}
