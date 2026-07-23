<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->string('employee_type')->default('expat')->after('employee_id');
            $table->string('national_id')->nullable()->after('iqama_image');
            $table->date('national_id_expiry_date')->nullable()->after('national_id');
            $table->string('national_id_image')->nullable()->after('national_id_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn(['employee_type', 'national_id', 'national_id_expiry_date', 'national_id_image']);
        });
    }
};
