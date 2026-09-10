<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_settlement_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_termination_id')->unique();
            $table->unsignedInteger('company_id');
            $table->string('status')->default('draft');
            $table->string('policy_version');
            $table->json('inputs');
            $table->decimal('total_payable', 15, 2)->default(0);
            $table->decimal('total_recoverable', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->unsignedBigInteger('finalized_by')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status'], 'hr_settlement_company_status_idx');
        });

        Schema::create('hr_settlement_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('settlement_form_id');
            $table->string('code');
            $table->string('kind'); // payable | recoverable
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['settlement_form_id', 'kind'], 'hr_settlement_item_form_kind_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_settlement_line_items');
        Schema::dropIfExists('hr_settlement_forms');
    }
};
