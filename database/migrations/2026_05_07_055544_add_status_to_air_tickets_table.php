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
        Schema::table('air_tickets', function (Blueprint $table) {

            $table->string('status')->default('pending')->after('date');

            $table->text('reject_reason')->nullable()->after('status');

            $table->unsignedBigInteger('approved_by')->nullable()->after('reject_reason');

            $table->text('approve_reason')->nullable()->after('approved_by');

            // Optional foreign key
            // $table->foreign('approved_by')
            //     ->references('id')
            //     ->on('users')
            //     ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_tickets', function (Blueprint $table) {

            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'status',
                'reject_reason',
                'approved_by',
                'approve_reason',
            ]);
        });
    }
};
