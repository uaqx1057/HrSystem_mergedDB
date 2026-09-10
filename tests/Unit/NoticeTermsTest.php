<?php

namespace Tests\Unit;

use App\Support\NoticeTerms;
use Tests\TestCase;

class NoticeTermsTest extends TestCase
{
    public function test_immediate_exit_has_no_notice_and_leaves_on_the_base_date(): void
    {
        $terms = NoticeTerms::resolve('immediate', null, '2026-10-01');

        $this->assertSame('immediate', $terms['notice_type']);
        $this->assertNull($terms['notice_months']);
        $this->assertNull($terms['notice_start_date']);
        $this->assertSame('2026-10-01', $terms['last_working_date']);
    }

    public function test_notice_period_adds_months_to_the_base_date(): void
    {
        foreach ([1 => '2026-11-01', 2 => '2026-12-01', 3 => '2027-01-01'] as $months => $expectedLwd) {
            $terms = NoticeTerms::resolve('notice', (string) $months, '2026-10-01');

            $this->assertSame('notice', $terms['notice_type']);
            $this->assertSame($months, $terms['notice_months']);
            $this->assertSame('2026-10-01', $terms['notice_start_date']);
            $this->assertSame($expectedLwd, $terms['last_working_date'], "month value $months");
        }
    }

    public function test_an_invalid_month_value_falls_back_to_one_month(): void
    {
        $terms = NoticeTerms::resolve('notice', '6', '2026-10-01');

        $this->assertSame(1, $terms['notice_months']);
        $this->assertSame('2026-11-01', $terms['last_working_date']);
    }
}
