<?php

namespace Tests\Unit;

use Tests\TestCase;

class HrSettlementPdfTest extends TestCase
{
    public function test_final_settlement_pdf_is_snapshot_based_and_idempotent(): void
    {
        $service = file_get_contents(app_path('Services/HrSettlementService.php'));
        $controller = file_get_contents(app_path('Http/Controllers/HrSettlementController.php'));
        $routes = file_get_contents(base_path('routes/web.php'));
        $view = file_get_contents(resource_path('views/hr-settlement/edit.blade.php'));

        $this->assertStringContainsString('function generatePdf(HrSettlementForm $settlement)', $service);
        $this->assertStringContainsString('if ($settlement->pdf_path && $settlement->document_hash)', $service);
        $this->assertStringContainsString('hash(\'sha256\', $bytes)', $service);
        $this->assertStringContainsString('Storage::download($settlement->pdf_path', $controller);
        $this->assertStringContainsString("hr-settlement/{termination}/pdf", $routes);
        $this->assertStringContainsString("route('hr-settlement.pdf'", $view);
    }
}
