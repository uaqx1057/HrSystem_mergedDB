<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();
            // Foreign key to drivers table
            $table->unsignedBigInteger('driver_id');

            // Document type
            $table->enum('document_type', [
                'iqama',
                'passport',
                'visa',
                'license',
                'medical',
                'contract',
                'mobile',
                'other',
            ]);

            // File information
            $table->text('file_path');
            $table->text('original_name');
            $table->unsignedInteger('file_size')->nullable();

            // Upload source
            $table->enum('uploaded_from', [
                'dms',
                'dobs',
                'hr'
            ]);

            // ID of user/employee who uploaded
            $table->unsignedBigInteger('uploaded_by')->nullable();

            // Additional notes
            $table->text('notes')->nullable();

            // Expiry date for applicable documents
            $table->date('expires_at')->nullable();

            // Soft delete
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_documents');
    }
};
