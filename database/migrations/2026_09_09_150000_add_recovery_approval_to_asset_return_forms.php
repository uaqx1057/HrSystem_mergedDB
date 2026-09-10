<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_return_forms', function (Blueprint $table) {
            $table->string('recovery_status')->default('not_required')->after('recovery_reason');
            $table->decimal('approved_recovery_amount', 15, 2)->nullable()->after('recommended_recovery_amount');
            $table->unsignedBigInteger('recovery_approved_by')->nullable()->after('certified_by');
            $table->timestamp('recovery_approved_at')->nullable()->after('recovery_approved_by');
            $table->index(['company_id', 'recovery_status'], 'asset_return_form_recovery_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('asset_return_forms', function (Blueprint $table) {
            $table->dropIndex('asset_return_form_recovery_status_idx');
            $table->dropColumn(['recovery_status', 'approved_recovery_amount', 'recovery_approved_by', 'recovery_approved_at']);
        });
    }
};
