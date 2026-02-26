<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_employee_setups', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable()->index();
            $table->unsignedInteger('user_id')->index();
            $table->double('basic_salary', 16, 2)->default(0);
            $table->double('housing_allowance', 16, 2)->default(0);
            $table->double('travel_allowance', 16, 2)->default(0);
            $table->double('opening_balance', 16, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'user_id'], 'payroll_employee_setups_company_user_unique');
        });

        Schema::create('payroll_driver_setups', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('driver_id')->index();
            $table->double('basic_salary', 16, 2)->default(0);
            $table->double('accommodation_allowance', 16, 2)->default(0);
            $table->double('car_allowance', 16, 2)->default(0);
            $table->double('opening_balance', 16, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'driver_id'], 'payroll_driver_setups_company_driver_unique');
        });

        Schema::table('salary_slips', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_slips', 'payee_type')) {
                $table->string('payee_type', 20)->default('employee')->after('user_id');
            }

            if (!Schema::hasColumn('salary_slips', 'paid_amount')) {
                $table->double('paid_amount', 16, 2)->default(0)->after('gross_salary');
            }

            if (!Schema::hasColumn('salary_slips', 'balance_amount')) {
                $table->double('balance_amount', 16, 2)->default(0)->after('paid_amount');
            }

            $table->index(['company_id', 'payee_type', 'user_id', 'month', 'year'], 'salary_slips_company_payee_period_idx');
        });
    }

    public function down(): void
    {
        Schema::table('salary_slips', function (Blueprint $table) {
            if (Schema::hasColumn('salary_slips', 'balance_amount')) {
                $table->dropColumn('balance_amount');
            }

            if (Schema::hasColumn('salary_slips', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }

            if (Schema::hasColumn('salary_slips', 'payee_type')) {
                $table->dropColumn('payee_type');
            }

            $table->dropIndex('salary_slips_company_payee_period_idx');
        });

        Schema::dropIfExists('payroll_driver_setups');
        Schema::dropIfExists('payroll_employee_setups');
    }
};
