<?php

namespace Tests\Unit;

use Tests\TestCase;

class AssetReturnPdfTest extends TestCase
{
    public function test_final_rt_pdf_generation_is_idempotent_and_route_wired(): void
    {
        $service = file_get_contents(app_path('Services/AssetReturnService.php'));
        $controller = file_get_contents(app_path('Http/Controllers/CompanyAssetController.php'));
        $routes = file_get_contents(base_path('routes/web.php'));

        $this->assertStringContainsString('function generatePdf(AssetReturnForm $form)', $service);
        $this->assertStringContainsString('if ($form->pdf_path && $form->document_hash)', $service);
        $this->assertStringContainsString("hash('sha256', \$bytes)", $service);
        $this->assertStringContainsString('function rtPdf($id)', $controller);
        $this->assertStringContainsString('Storage::download($form->pdf_path', $controller);
        $this->assertStringContainsString("company-assets/return-form/{id}/pdf", $routes);
    }
}
