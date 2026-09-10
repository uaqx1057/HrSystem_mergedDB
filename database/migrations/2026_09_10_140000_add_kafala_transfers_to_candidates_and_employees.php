<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How many times an expat applicant's sponsorship (kafala) has already been
 * transferred - captured on the public apply form and carried onto the
 * employee record on conversion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_candidates', 'kafala_transfers')) {
                $table->unsignedTinyInteger('kafala_transfers')->nullable()->after('marital_status');
            }
        });

        Schema::table('employee_details', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_details', 'no_of_kafala_transfers')) {
                $table->unsignedTinyInteger('no_of_kafala_transfers')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_candidates', function (Blueprint $table) {
            $table->dropColumn('kafala_transfers');
        });
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn('no_of_kafala_transfers');
        });
    }
};
