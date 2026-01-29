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
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('invoice_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('driver_id')->nullable();
            $table->integer('business_id')->nullable();
            $table->string('other_business')->nullable();
            $table->decimal('wallet_amount', 8, 2)->nullable();
            $table->decimal('total_amount', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_date');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('driver_id');
            $table->dropColumn('business_id');
            $table->dropColumn('other_business');
            $table->dropColumn('wallet_amount');
            $table->dropColumn('total_amount');
        });
    }
};
