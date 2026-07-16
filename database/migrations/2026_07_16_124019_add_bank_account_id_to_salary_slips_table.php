<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_slips', function (Blueprint $table) {
            $table->foreignId('employee_bank_account_id')
                ->nullable()
                ->after('salary_payment_method_id')
                ->constrained('employee_bank_accounts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salary_slips', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_bank_account_id');
        });
    }
};

