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
        Schema::table('drivers', function (Blueprint $table) {
            $table->date('insurance_expiry_date')->nullable()->change();
            $table->date('license_expiry_date')->nullable()->change();
            $table->date('iqaama_expiry_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->date('insurance_expiry_date')->change();
            $table->date('license_expiry_date')->change();
            $table->date('iqaama_expiry_date')->change();
        });
    }
};
