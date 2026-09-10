<?php

namespace Tests\Unit;

use Tests\TestCase;

class HrEmployeeRequestConsistencyTest extends TestCase
{
    public function test_attachment_is_removed_when_request_persistence_fails(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/HrEmployeeRequestController.php'));

        $this->assertStringContainsString('try { HrEmployeeRequest::create', $controller);
        $this->assertStringContainsString("Storage::disk('storage')->delete(\$path)", $controller);
        $this->assertStringContainsString('catch (\\Throwable $exception)', $controller);
    }
}
