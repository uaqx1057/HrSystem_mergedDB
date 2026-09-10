<?php

namespace Tests\Unit;

use App\Models\AssetReturnForm;
use Tests\TestCase;

class AssetReturnServiceTest extends TestCase
{
    public function test_return_flow_is_wired_to_the_canonical_service(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/CompanyAssetController.php'));

        $this->assertStringContainsString('app(AssetReturnService::class)->certifyReturn($assignment, user()->id', $controller);
        $this->assertStringContainsString('app(AssetReturnService::class)->writeOff($serial, user()->id', $controller);
        $this->assertStringContainsString('function writeOffAssignment(Request $request, $id)', $controller);
    }

    public function test_write_off_only_allows_terminal_disposition_outcomes(): void
    {
        $service = file_get_contents(app_path('Services/AssetReturnService.php'));

        $this->assertStringContainsString('AssetReturnForm::OUTCOME_LOST, AssetReturnForm::OUTCOME_DAMAGED, AssetReturnForm::OUTCOME_RETIRED', $service);
        $this->assertStringContainsString("'status' => AssetReturnForm::STATUS_FINAL", $service);
    }

    public function test_return_form_reference_and_outcome_constants_are_stable(): void
    {
        $this->assertSame('returned', AssetReturnForm::OUTCOME_RETURNED);
        $this->assertSame('lost', AssetReturnForm::OUTCOME_LOST);
        $this->assertSame('damaged', AssetReturnForm::OUTCOME_DAMAGED);
        $this->assertSame('retired', AssetReturnForm::OUTCOME_RETIRED);
        $this->assertSame('final', AssetReturnForm::STATUS_FINAL);
    }
}
