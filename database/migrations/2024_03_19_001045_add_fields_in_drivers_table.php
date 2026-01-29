<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('iqama')->nullable();
            $table->string('license')->nullable();
            $table->string('mobile_form')->nullable();
            $table->string('other_document')->nullable();
            $table->string('sim_form')->nullable();
            $table->string('medical')->nullable();
            $table->string('stc_pay')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('iban')->nullable();
            $table->string('department')->nullable();
            $table->string('job_position')->nullable();
            $table->integer('contract_period_in_months')->nullable();
            $table->date('joining_date')->nullable();
            $table->double('basic_salary')->nullable();
            $table->double('housing_allowance')->nullable();
            $table->double('transportation_allowance')->nullable();
            $table->double('performance_allowance')->nullable();
            $table->double('other_allowance')->nullable();
            $table->double('total_salary')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('iqama');
            $table->dropColumn('license');
            $table->dropColumn('mobile_form');
            $table->dropColumn('other_document');
            $table->dropColumn('sim_form');
            $table->dropColumn('medical');
            $table->dropColumn('stc_pay');
            $table->dropColumn('bank_name');
            $table->dropColumn('iban');
            $table->dropColumn('contract_period_in_months');
            $table->dropColumn('job_position');
            $table->dropColumn('department');
            $table->dropColumn('joining_date');
            $table->dropColumn('basic_salary');
            $table->dropColumn('housing_allowance');
            $table->dropColumn('transportation_allowance');
            $table->dropColumn('performance_allowance');
            $table->dropColumn('other_allowance');
            $table->dropColumn('total_salary');
        });
    }
};
