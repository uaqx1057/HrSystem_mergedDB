<?php

namespace Tests\Unit;

use Tests\TestCase;

class AccessRevocationSequencingTest extends TestCase
{
    public function test_approved_offboarding_enqueues_access_revocation_and_completion_requires_it(): void
    {
        $service = file_get_contents(app_path('Services/OffboardingService.php'));
        $job = file_get_contents(app_path('Jobs/ProcessHrSystemSyncJob.php'));
        $employeeSync = file_get_contents(app_path('Services/EmployeeSystemSyncService.php'));
        $controller = file_get_contents(app_path('Http/Controllers/EmployeeController.php'));

        $this->assertStringContainsString("'operation' => 'revoke_access'", $service);
        $this->assertStringContainsString("\$syncJob->operation === 'revoke_access'", $job);
        $this->assertStringContainsString('revokeLinkedSystemAccess', $employeeSync);
        $this->assertStringContainsString('access_revoked_at', $job);
        $this->assertStringContainsString('open_required_tasks', $controller);
    }
}
