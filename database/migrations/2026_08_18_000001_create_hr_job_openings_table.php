<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_job_openings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->string('title');
            // teams/branches/users all use INT UNSIGNED ids (increments()), not foreignId()'s BIGINT UNSIGNED.
            $table->unsignedInteger('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            // No FK constraint on branches — same precedent as drivers.branch_id (see
            // 2024_05_22_151258_add_branch_id_field_in_drivers_table.php); the live branches.id
            // type doesn't reliably match across environments here.
            $table->unsignedInteger('branch_id')->nullable();
            $table->string('employment_type')->nullable();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->unsignedInteger('positions_count')->default(1);
            $table->string('status')->default('open');
            $table->string('public_slug')->unique();
            // No FK constraint on users — same errno 150 as branches above.
            $table->unsignedInteger('created_by')->nullable();
            $table->date('closes_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_job_openings');
    }
};
