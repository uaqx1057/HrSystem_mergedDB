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
        Schema::table('employee_details', function (Blueprint $table) {

            $table->string('transfer_number')->nullable();

            $table->string('probation_time')->nullable();

            $table->text('qiva_contract')->nullable();

            $table->text('company_contract')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {

            $table->dropColumn([
                'transfer_number',
                'probation_time',
                'qiva_contract',
                'company_contract',
            ]);
        });
    }
};
