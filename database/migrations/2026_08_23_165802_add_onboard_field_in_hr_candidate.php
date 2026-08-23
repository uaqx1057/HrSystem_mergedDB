<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_candidates', function (Blueprint $table) {
            $table->string('employee_type')->nullable()->after('basic_salary');
            $table->string('iqama_no')->nullable()->after('employee_type');
            $table->string('iqama_profession')->nullable()->after('iqama_no');
            $table->date('iqama_expiry_date')->nullable()->after('iqama_profession');
            $table->string('national_id')->nullable()->after('iqama_expiry_date');
            $table->date('national_id_expiry_date')->nullable()->after('national_id');
            $table->string('passport_no')->nullable()->after('national_id_expiry_date');
            $table->date('passport_expiry_date')->nullable()->after('passport_no');
            $table->string('probation_time')->nullable()->after('passport_expiry_date');
            $table->string('bank_name')->nullable()->after('probation_time');
            $table->string('iban_number')->nullable()->after('bank_name');
            $table->string('account_number')->nullable()->after('iban_number');
            $table->string('swift_code')->nullable()->after('account_number');
        });
    }

    public function down(): void
    {
        Schema::table('hr_candidates', function (Blueprint $table) {
            $table->dropColumn([
                'employee_type', 'iqama_no', 'iqama_profession', 'iqama_expiry_date',
                'national_id', 'national_id_expiry_date', 'passport_no', 'passport_expiry_date',
                'probation_time', 'bank_name', 'iban_number', 'account_number', 'swift_code',
            ]);
        });
    }
};