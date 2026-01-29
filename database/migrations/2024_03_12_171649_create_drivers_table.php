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
        if (!Schema::hasTable('drivers')) {
            Schema::create('drivers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('company_id')->unsigned()->nullable();
                $table->integer('nationality_id')->unsigned()->nullable();
                $table->string('name', 155);
                $table->string('iqaama_number');
                $table->string('absher_number');
                $table->string('sponsorship');
                $table->string('sponsorship_id');
                $table->string('insurance_policy_number');
                $table->string('address')->nullable();
                $table->text('remarks')->nullable();
                $table->string('email');
                $table->string('work_mobile_no');
                $table->date('insurance_expiry_date');
                $table->date('license_expiry_date');
                $table->date('iqaama_expiry_date');
                $table->date('date_of_birth');

                $table->foreign('nationality_id')->references('id')->on('countries')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
