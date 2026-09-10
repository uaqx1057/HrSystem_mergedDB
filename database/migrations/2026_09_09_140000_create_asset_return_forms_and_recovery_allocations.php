<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_return_forms', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();
            $table->unsignedInteger('company_id');
            $table->unsignedBigInteger('company_asset_id');
            $table->unsignedBigInteger('company_asset_serial_id')->nullable();
            $table->unsignedBigInteger('asset_assignment_id')->nullable();
            $table->unsignedBigInteger('asset_assignment_history_id')->nullable();
            $table->unsignedBigInteger('employee_id');

            // Outcome of this unit's return: formally returned vs. written off.
            $table->string('outcome'); // returned | lost | damaged | retired
            $table->text('accessories_checklist')->nullable();
            $table->text('technical_inspection')->nullable();
            $table->text('data_clearance_notes')->nullable();
            $table->text('disposition_notes')->nullable();
            $table->decimal('recommended_recovery_amount', 15, 2)->nullable();
            $table->text('recovery_reason')->nullable();

            $table->string('status')->default('draft'); // draft | final
            $table->unsignedBigInteger('certified_by')->nullable();
            $table->timestamp('certified_at')->nullable();
            $table->string('evidence_path')->nullable();
            $table->string('signed_document')->nullable();
            $table->json('snapshot')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('document_hash')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'reference'], 'asset_return_form_company_ref_unique');
            $table->index(['company_asset_id', 'status'], 'asset_return_form_asset_status_idx');
            $table->index(['employee_id', 'outcome'], 'asset_return_form_employee_outcome_idx');
        });

        Schema::create('asset_recovery_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_return_form_id');
            $table->unsignedBigInteger('employee_assess_loss_id')->nullable();
            $table->string('method'); // payroll | settlement | waiver | external_payment
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['asset_return_form_id', 'method'], 'asset_recovery_alloc_form_method_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_recovery_allocations');
        Schema::dropIfExists('asset_return_forms');
    }
};
