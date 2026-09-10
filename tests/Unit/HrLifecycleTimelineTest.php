<?php

namespace Tests\Unit;

use Tests\TestCase;

class HrLifecycleTimelineTest extends TestCase
{
    public function test_employee_lifecycle_screen_exposes_audited_events(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/HrLifecycleController.php'));
        $view = file_get_contents(resource_path('views/hr-lifecycle/show.blade.php'));

        $this->assertStringContainsString('HrLifecycleEvent::with', $controller);
        $this->assertStringContainsString('where(\'subject_user_id\', $employeeId)', $controller);
        $this->assertStringContainsString('Lifecycle timeline', $view);
        $this->assertStringContainsString('$event->actor?->name', $view);
    }
}
