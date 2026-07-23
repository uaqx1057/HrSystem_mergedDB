<?php

namespace Tests\Unit;

use Tests\TestCase;

class HrWorkflowRouteTest extends TestCase
{
    public function test_hr_workflow_routes_are_registered_with_the_expected_methods(): void
    {
        $routes = app('router')->getRoutes();
        $expected = [
            'hr-candidates.index' => 'GET',
            'hr-candidates.handoff' => 'POST',
            'hr-lifecycle.index' => 'GET',
            'hr-lifecycle.onboarding.start' => 'POST',
            'hr-lifecycle.offboarding.start' => 'POST',
            'hr-lifecycle.transfer.request' => 'POST',
            'hr-lifecycle.transfer.approve' => 'POST',
            'hr-lifecycle.transfer.apply' => 'POST',
            'hr-payroll-preflight.index' => 'GET',
            'hr-payroll-preflight.approve' => 'POST',
            'hr-compliance.index' => 'GET',
            'hr-certification-rules.store' => 'POST',
            'hr-employee-requests.index' => 'GET',
            'hr-employee-requests.review' => 'POST',
            'hr-role-worklists.finance' => 'GET',
            'hr-role-worklists.it' => 'GET',
        ];

        foreach ($expected as $name => $method) {
            $route = $routes->getByName($name);
            $this->assertNotNull($route, "Missing HR route: {$name}");
            $this->assertContains($method, $route->methods(), "Wrong method for HR route: {$name}");
        }
    }
}
