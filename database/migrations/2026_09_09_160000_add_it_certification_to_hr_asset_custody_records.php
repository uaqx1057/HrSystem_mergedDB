<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_asset_custody_records', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_return_form_id')->nullable()->after('asset_assignment_id');
            $table->unsignedBigInteger('certified_by')->nullable()->after('returned_at');
            $table->timestamp('certified_at')->nullable()->after('certified_by');
            $table->text('certification_notes')->nullable()->after('certified_at');
            $table->index(['returned_at', 'certified_at'], 'hr_custody_return_cert_idx');
        });
    }

    public function down(): void
    {
        Schema::table('hr_asset_custody_records', function (Blueprint $table) {
            $table->dropIndex('hr_custody_return_cert_idx');
            $table->dropColumn(['asset_return_form_id', 'certified_by', 'certified_at', 'certification_notes']);
        });
    }
};
