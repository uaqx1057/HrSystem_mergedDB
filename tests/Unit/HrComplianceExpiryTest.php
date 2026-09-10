<?php

namespace Tests\Unit;

use Tests\TestCase;

class HrComplianceExpiryTest extends TestCase
{
    public function test_compliance_dashboard_maps_expiry_sources_and_bands(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/HrComplianceController.php'));
        $expiry = file_get_contents(app_path('Support/HrDocumentExpiry.php'));
        $view = file_get_contents(resource_path('views/hr-compliance/index.blade.php'));

        // The dashboard delegates to the shared expiry source so the worklist and
        // the compliance page can never drift apart again.
        $this->assertStringContainsString('HrDocumentExpiry::forEmployees', $controller);
        $this->assertStringContainsString('Passport::where', $expiry);
        $this->assertStringContainsString("'Iqama / Visa'", $expiry);
        $this->assertStringContainsString("'Insurance'", $expiry);
        $this->assertStringContainsString("'Contract'", $expiry);
        $this->assertStringContainsString("employeeDetail?->contract_end_date", $expiry);
        // The Worksuite client/sales `contracts` table (client_id) must never be
        // used as an employee document source.
        $this->assertStringNotContainsString("whereIn('client_id'", $expiry);
        $this->assertStringContainsString("\$days <= 30 ? '0-30'", $expiry);
        $this->assertStringContainsString("name=\"expiry_band\"", $view);
        $this->assertStringContainsString("name=\"expiry_type\"", $view);
    }
}
