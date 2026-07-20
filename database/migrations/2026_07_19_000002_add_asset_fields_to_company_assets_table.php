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
        Schema::table('company_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('brand');
            $table->unsignedBigInteger('branch_id')->nullable()->after('department_id');
            $table->unsignedInteger('qty')->default(0)->after('branch_id');
            $table->unsignedInteger('available_qty')->default(0)->after('qty');
            $table->string('status')->default('available')->after('available_qty');

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_assets', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['department_id', 'branch_id', 'qty', 'available_qty', 'status']);
        });
    }
};
