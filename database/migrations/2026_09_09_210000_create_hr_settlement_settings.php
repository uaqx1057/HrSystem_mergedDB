<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-company end-of-service / settlement policy. Seeded with the Saudi Labour
 * Law statutory defaults (Articles 84 & 85) but every figure is editable and the
 * engine output is flagged provisional until HR/legal sign off.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_settlement_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->unique();

            // Which payroll components form the "wage" for EOSB / leave encashment.
            $table->string('eosb_wage_basis')->default('gross'); // basic | basic_housing | gross

            // Article 84 award: month-fractions of wage per year of service.
            $table->decimal('award_first_5yr_month_fraction', 6, 4)->default(0.5);
            $table->decimal('award_after_5yr_month_fraction', 6, 4)->default(1.0);
            $table->boolean('termination_gets_full_award')->default(true); // false only for Art. 80 grounds

            // Article 85 resignation entitlement fractions of the Article 84 award.
            $table->decimal('resign_under_2yr_fraction', 6, 4)->default(0.0);
            $table->decimal('resign_2_to_5yr_fraction', 6, 4)->default(0.3333);
            $table->decimal('resign_5_to_10yr_fraction', 6, 4)->default(0.6667);
            $table->decimal('resign_10yr_plus_fraction', 6, 4)->default(1.0);

            // Leave encashment.
            $table->boolean('encash_leave_on_exit')->default(true);
            $table->unsignedInteger('default_annual_leave_days')->default(21);
            $table->unsignedInteger('leave_daily_wage_divisor')->default(30);

            $table->string('policy_version')->default('provisional-2026-09-09');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_settlement_settings');
    }
};
