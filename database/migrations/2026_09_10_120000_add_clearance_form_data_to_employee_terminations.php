<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structured payloads captured on the IT and Finance clearance screens so a
 * clearance can only be issued once the form is actually filled in.
 *
 *  - it_clearance_data       : clearance-level IT data & security confirmation,
 *                              inspector name, IT remarks.
 *  - finance_clearance_data  : 9-point verification checklist answers, payment
 *                              method, bank name / IBAN, finance remarks, names.
 *  - *_clearance_decision    : the outcome the officer picked on the form.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_terminations', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_terminations', 'it_clearance_data')) {
                $table->json('it_clearance_data')->nullable()->after('it_reminder_sent_at');
            }
            if (!Schema::hasColumn('employee_terminations', 'it_clearance_decision')) {
                $table->string('it_clearance_decision')->nullable()->after('it_clearance_data');
            }
            if (!Schema::hasColumn('employee_terminations', 'finance_clearance_data')) {
                $table->json('finance_clearance_data')->nullable()->after('finance_reminder_sent_at');
            }
            if (!Schema::hasColumn('employee_terminations', 'finance_clearance_decision')) {
                $table->string('finance_clearance_decision')->nullable()->after('finance_clearance_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_terminations', function (Blueprint $table) {
            $table->dropColumn([
                'it_clearance_data',
                'it_clearance_decision',
                'finance_clearance_data',
                'finance_clearance_decision',
            ]);
        });
    }
};
