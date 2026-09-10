<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_settlement_forms', function (Blueprint $table) {
            $table->json('snapshot')->nullable()->after('inputs');
            $table->string('pdf_path')->nullable()->after('finalized_at');
            $table->string('document_hash')->nullable()->after('pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('hr_settlement_forms', function (Blueprint $table) {
            $table->dropColumn(['snapshot', 'pdf_path', 'document_hash']);
        });
    }
};
