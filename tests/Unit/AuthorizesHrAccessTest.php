<?php

namespace Tests\Unit;

use Tests\TestCase;

class AuthorizesHrAccessTest extends TestCase
{
    public function test_shared_hr_authorization_trait_is_adopted_by_worklist(): void
    {
        $trait = file_get_contents(app_path('Traits/AuthorizesHrAccess.php'));
        $controller = file_get_contents(app_path('Http/Controllers/HrWorklistController.php'));

        $this->assertStringContainsString('trait AuthorizesHrAccess', $trait);
        $this->assertStringContainsString('HrAccess::canAccessEmployeeBranch', $trait);
        $this->assertStringContainsString('use AuthorizesHrAccess;', $controller);
        $this->assertStringContainsString("authorizeHrPermission('edit_employees'", $controller);
    }
}
