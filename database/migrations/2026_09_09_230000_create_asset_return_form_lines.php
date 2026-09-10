<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structured checklist rows for the RT return form: accessories, technical
 * evaluation, and data/security clearance. The free-text columns on
 * asset_return_forms stay as a rendered summary / fallback.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_return_form_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_return_form_id');
            $table->string('section', 20);   // accessories | technical | data
            $table->string('label');
            $table->string('result', 40)->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['asset_return_form_id', 'section'], 'arfl_form_section_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_return_form_lines');
    }
};
