<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            // NEW FIELDS
            $table->string('iqama_designation')->nullable()->after('iqama_no');
            $table->string('iqama_profession')->nullable()->after('iqama_designation');
            $table->string('iqama_image')->nullable()->after('iqama_expiry_date');
            $table->string('passport_no')->nullable()->after('iqama_image');
            $table->date('passport_expiry_date')->nullable()->after('passport_no');
            $table->string('passport_image')->nullable()->after('passport_expiry_date');
           $table->date('sponsorship_transfer_date')->nullable()->after('sponsor_kafala');
            $table->integer('no_of_dependants')->nullable()->after('marital_status');

            // DROP old unused columns
            $table->dropColumn('marriage_anniversary_date');
        });

        // Remove skills table relation if needed (employee_skills stays, just removing from UI)
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn([
                'iqama_designation',
                'iqama_profession',
                'iqama_image',
                'passport_no',
                'passport_expiry_date',
                'passport_image',
                'sponsor_kafala',
                'sponsorship_transfer_date',
                'no_of_dependants',
            ]);
            $table->date('marriage_anniversary_date')->nullable();
        });
    }
};