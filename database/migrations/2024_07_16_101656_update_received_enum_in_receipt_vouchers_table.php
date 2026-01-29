<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up()
    {
        Schema::table('receipt_vouchers', function (Blueprint $table) {
            // Change the 'received' column to string temporarily
            $table->string('status')->default('status')->change();
        });

        // Update the column with new and old enum values
        DB::statement("ALTER TABLE receipt_vouchers MODIFY COLUMN status ENUM('paid', 'unpaid', 'cancelled', 'draft','signed', 'deposited', 'approved', 'received') DEFAULT 'received'");
    }

    public function down()
    {
        Schema::table('receipt_vouchers', function (Blueprint $table) {
            // Revert the 'received' column to string temporarily
            $table->string('status')->default('status')->change();
        });

        // Revert the column to original enum values
        DB::statement("ALTER TABLE receipt_vouchers MODIFY COLUMN status ENUM('paid','unpaid','partial','canceled','draft') DEFAULT 'unpaid'");
    }
};
