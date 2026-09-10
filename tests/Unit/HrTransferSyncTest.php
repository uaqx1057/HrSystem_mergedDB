<?php

namespace Tests\Unit;

use Tests\TestCase;

class HrTransferSyncTest extends TestCase
{
    public function test_transfer_application_dispatches_linked_system_sync_after_commit(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/HrLifecycleController.php'));

        $this->assertStringContainsString("'operation' => 'transfer_branch_sync'", $controller);
        $this->assertStringContainsString('ProcessHrSystemSyncJob::dispatch($syncJob->id)->afterCommit()', $controller);
        $this->assertStringContainsString("'systems' => ['dms', 'dobs']", $controller);
    }
}
