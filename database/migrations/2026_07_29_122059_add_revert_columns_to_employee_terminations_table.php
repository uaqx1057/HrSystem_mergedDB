<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employee_terminations', function (Blueprint $table) {
            $table->unsignedBigInteger('reverted_by')->nullable()->after('completed_at');
            $table->timestamp('reverted_at')->nullable()->after('reverted_by');
            $table->text('revert_reason')->nullable()->after('reverted_at');

            $table->foreign('reverted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('employee_terminations', function (Blueprint $table) {
            $table->dropForeign(['reverted_by']);
            $table->dropColumn(['reverted_by', 'reverted_at', 'revert_reason']);
        });
    }
};
