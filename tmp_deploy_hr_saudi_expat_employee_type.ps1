$ErrorActionPreference = 'Stop'
Import-Module Posh-SSH

Set-Location $PSScriptRoot

$server = '130.94.59.87'
$user = 'root'
$passwordPlain = 'FussionHost@68373898'
$password = ConvertTo-SecureString $passwordPlain -AsPlainText -Force
$credential = New-Object System.Management.Automation.PSCredential($user, $password)
$release = '/srv/apps/releases/HrSystem_mergedDB'
$backupDir = "$release/deploy-backups/$(Get-Date -Format 'yyyyMMdd_HHmmss')_saudi_expat_employee_type"
$stageRoot = "/tmp/hr-saudi-expat-$(Get-Date -Format 'yyyyMMdd_HHmmss')"
$remoteProbePath = '/tmp/hr_saudi_expat_probe.php'

$deployFiles = @(
    'app/Http/Controllers/EmployeeController.php',
    'app/Http/Requests/Admin/Employee/StoreRequest.php',
    'app/Http/Requests/Admin/Employee/UpdateRequest.php',
    'database/migrations/2026_07_21_000001_add_national_id_fields_to_employee_details_table.php',
    'resources/lang/eng/modules.php',
    'resources/lang/eng/placeholders.php',
    'resources/views/employees/ajax/create.blade.php',
    'resources/views/employees/ajax/edit.blade.php',
    'resources/views/employees/ajax/profile.blade.php'
)

function Invoke-RemoteCommand {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Command,
        [switch] $AllowFailure
    )

    $session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey -Force
    try {
        $normalizedCommand = $Command -replace "`r", ''
        $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $normalizedCommand

        if ($result.Output) {
            $result.Output | ForEach-Object { Write-Host $_ }
        }

        if ($result.Error) {
            $result.Error | ForEach-Object { Write-Host $_ }
        }

        if (-not $AllowFailure -and $result.ExitStatus -ne 0) {
            throw "Remote command failed with exit code $($result.ExitStatus): $Command"
        }

        return $result
    }
    finally {
        Remove-SSHSession -SSHSession $session | Out-Null
    }
}

function Get-RemoteDirectory {
    param(
        [Parameter(Mandatory = $true)]
        [string] $BasePath,
        [Parameter(Mandatory = $true)]
        [string] $RelativeFilePath
    )

    $pathParts = $RelativeFilePath -split '/'
    if ($pathParts.Length -le 1) {
        return $BasePath.TrimEnd('/')
    }

    return ($BasePath.TrimEnd('/') + '/' + ($pathParts[0..($pathParts.Length - 2)] -join '/'))
}

$probeScript = @'
<?php
require '/srv/apps/releases/HrSystem_mergedDB/vendor/autoload.php';
$app = require '/srv/apps/releases/HrSystem_mergedDB/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EmployeeDetails;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

function out(string $key, $value): void
{
    if (is_bool($value)) {
        $value = $value ? 'yes' : 'no';
    }

    echo $key . '=' . (is_scalar($value) || $value === null ? (string) $value : json_encode($value)) . PHP_EOL;
}

function fail(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

out('has_employee_type_column', Schema::hasColumn('employee_details', 'employee_type'));
out('has_national_id_column', Schema::hasColumn('employee_details', 'national_id'));
out('has_national_id_expiry_column', Schema::hasColumn('employee_details', 'national_id_expiry_date'));
out('has_national_id_image_column', Schema::hasColumn('employee_details', 'national_id_image'));

$existingDetail = EmployeeDetails::query()->orderBy('id')->first();
if ($existingDetail) {
    out('existing_row_default_employee_type', $existingDetail->employee_type);
}

$probeUser = User::query()->where('role_id', 1)->orderBy('id')->first()
    ?: User::query()->orderBy('id')->first();

if (!$probeUser) {
    fail('No user available for probes.');
}

$probeAuth = \App\Models\UserAuth::find($probeUser->user_auth_id);
if (!$probeAuth) {
    fail('No matching UserAuth record for probe user ' . $probeUser->id);
}
Auth::guard()->setUser($probeAuth);
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$employeeWithDetail = User::query()
    ->whereHas('employeeDetail')
    ->with('employeeDetail')
    ->first();

$targets = [
    ['uri' => '/employees/create', 'label' => 'employees_create'],
];

if ($employeeWithDetail) {
    out('probe_employee_id', $employeeWithDetail->id);
    $targets[] = ['uri' => '/employees/' . $employeeWithDetail->id . '/edit', 'label' => 'employees_edit'];
    $targets[] = ['uri' => '/employees/' . $employeeWithDetail->id, 'label' => 'employees_show'];
} else {
    out('probe_employee_id', 'none');
}

foreach ($targets as $target) {
    $request = Request::create($target['uri'], 'GET');
    $request->server->set('HTTP_HOST', 'hr.speedlogi.sa');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    Auth::guard()->setUser($probeAuth);
    $request->setUserResolver(fn() => $probeAuth);
    $response = $httpKernel->handle($request);
    $status = $response->getStatusCode();
    $content = (string) $response->getContent();
    $httpKernel->terminate($request, $response);
    out('route_status_' . $target['label'], $status);
    out('route_length_' . $target['label'], strlen($content));

    if ($status >= 500) {
        fail('HTTP route probe returned 500+ for ' . $target['uri'] . ': ' . substr($content, 0, 800));
    }

    if ($target['label'] === 'employees_create' || $target['label'] === 'employees_edit') {
        out('contains_employee_type_select_' . $target['label'], str_contains($content, 'id="employee_type"'));
        out('contains_national_id_field_' . $target['label'], str_contains($content, 'id="national_id"'));
        out('contains_saudi_only_class_' . $target['label'], str_contains($content, 'saudi-only-field'));
    }

    if ($target['label'] === 'employees_show') {
        out('contains_employee_type_label_show', str_contains($content, 'Employee Type'));
    }
}

exit(0);
'@

$probeBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($probeScript))

Write-Host "Backing up $($deployFiles.Count) files to $backupDir ..."
foreach ($relativePath in $deployFiles) {
    $releasePath = "$release/$relativePath"
    $backupPath = "$backupDir/$relativePath"
    $backupDirectory = Get-RemoteDirectory -BasePath $backupDir -RelativeFilePath $relativePath
    Invoke-RemoteCommand -Command "mkdir -p '$backupDirectory'; [ -f '$releasePath' ] && cp '$releasePath' '$backupPath' || echo 'no existing file: $relativePath'" -AllowFailure | Out-Null
}

Write-Host "Staging files under $stageRoot ..."
$sftp = New-SFTPSession -ComputerName $server -Credential $credential -AcceptKey -Force
try {
    foreach ($relativePath in $deployFiles) {
        $localPath = Join-Path $PSScriptRoot ($relativePath -replace '/', '\')
        $stageDirectory = Get-RemoteDirectory -BasePath $stageRoot -RelativeFilePath $relativePath
        Invoke-RemoteCommand -Command "mkdir -p '$stageDirectory'" | Out-Null
        Set-SFTPItem -SessionId $sftp.SessionId -Path $localPath -Destination $stageDirectory -Force
        Write-Host "Staged: $relativePath"
    }
}
finally {
    Remove-SFTPSession -SFTPSession $sftp | Out-Null
}

Invoke-RemoteCommand -Command "printf '%s' '$probeBase64' | base64 -d > '$remoteProbePath'; chmod 644 '$remoteProbePath'" | Out-Null

try {
    Write-Host 'Promoting files...'
    foreach ($relativePath in $deployFiles) {
        $releaseDirectory = Get-RemoteDirectory -BasePath $release -RelativeFilePath $relativePath
        $stagePath = "$stageRoot/$relativePath"
        $releasePath = "$release/$relativePath"
        $promoteCommand = @(
            "mkdir -p '$releaseDirectory'",
            "cp '$stagePath' '$releasePath'",
            "chown www-data:www-data '$releasePath'",
            "chmod 644 '$releasePath'"
        ) -join '; '
        Invoke-RemoteCommand -Command $promoteCommand | Out-Null
        Write-Host "Promoted: $relativePath"
    }

    Write-Host 'Syntax-checking PHP files...'
    foreach ($relativePath in $deployFiles) {
        if ($relativePath.EndsWith('.php')) {
            Invoke-RemoteCommand -Command "php -l '$release/$relativePath'" | Out-Null
        }
    }

    Write-Host 'Running new migration...'
    Invoke-RemoteCommand -Command "cd '$release'; sudo -u www-data php artisan migrate --force" | Out-Null

    Write-Host 'Clearing and rebuilding caches...'
    Invoke-RemoteCommand -Command "cd '$release'; sudo -u www-data php artisan optimize:clear; sudo -u www-data php artisan config:cache; sudo -u www-data php artisan view:cache" | Out-Null

    Write-Host 'Running functional probe...'
    Invoke-RemoteCommand -Command "cd '$release'; sudo -u www-data php '$remoteProbePath'" | Out-Null

    Write-Host 'Reloading php-fpm (hr pool)...'
    Invoke-RemoteCommand -Command "systemctl reload php8.4-fpm; systemctl is-active php8.4-fpm" | Out-Null

    Write-Host 'Checking recent production errors...'
    $logCheckCommand = @"
cd '$release'
sudo -u www-data php -r '
`$path = "$release/storage/logs/laravel.log";
if (!file_exists(`$path)) {
    echo "laravel_log=missing" . PHP_EOL;
    exit(0);
}
`$lines = @file(`$path, FILE_IGNORE_NEW_LINES) ?: [];
`$recent = array_values(array_filter(array_slice(`$lines, -200), fn (`$line) => str_contains(`$line, "production.ERROR")));
echo "recent_production_error_count=" . count(`$recent) . PHP_EOL;
if (`$recent) {
    echo "recent_last_error=" . substr(end(`$recent), 0, 500) . PHP_EOL;
}
'
"@
    Invoke-RemoteCommand -Command $logCheckCommand | Out-Null
}
catch {
    Write-Host 'Deployment failed, restoring backups (code only, NOT the migration)...'
    foreach ($relativePath in $deployFiles) {
        try {
            Invoke-RemoteCommand -Command "cp '$backupDir/$relativePath' '$release/$relativePath' && chown www-data:www-data '$release/$relativePath'" -AllowFailure | Out-Null
        }
        catch {
        }
    }
    Invoke-RemoteCommand -Command "cd '$release'; sudo -u www-data php artisan optimize:clear" -AllowFailure | Out-Null
    throw
}
finally {
    Invoke-RemoteCommand -Command "rm -rf '$stageRoot' '$remoteProbePath'" -AllowFailure | Out-Null
}

Write-Host 'HR SAUDI/EXPAT EMPLOYEE TYPE DEPLOY COMPLETE'
Write-Host "Backup retained at: $backupDir"
