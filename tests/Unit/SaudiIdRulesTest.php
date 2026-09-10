<?php

namespace Tests\Unit;

use App\Support\SaudiIdRules;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SaudiIdRulesTest extends TestCase
{
    /** @dataProvider iqamaCases */
    public function test_iqama_rule(string $value, bool $valid): void
    {
        $fails = Validator::make(['iqama_no' => $value], ['iqama_no' => SaudiIdRules::iqama()])->fails();
        $this->assertSame($valid, !$fails, "iqama '$value'");
    }

    /** @dataProvider nationalIdCases */
    public function test_national_id_rule(string $value, bool $valid): void
    {
        $fails = Validator::make(['national_id' => $value], ['national_id' => SaudiIdRules::nationalId()])->fails();
        $this->assertSame($valid, !$fails, "national_id '$value'");
    }

    public static function iqamaCases(): array
    {
        return [
            ['2123456789', true],
            ['2000000000', true],
            ['1123456789', false],   // wrong prefix
            ['212345678', false],    // 9 digits
            ['21234567890', false],  // 11 digits
            ['2abc456789', false],   // non-numeric
            ['', false],             // required
        ];
    }

    public static function nationalIdCases(): array
    {
        return [
            ['1123456789', true],
            ['1000000000', true],
            ['2123456789', false],
            ['112345678', false],
            ['11234567890', false],
            ['', false],
        ];
    }
}
