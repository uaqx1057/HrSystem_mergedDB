<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('dobs_user') && !$schema->hasColumn('dobs_user', 'is_login_allowed')) {
            $schema->table('dobs_user', function (Blueprint $table) {
                $table->boolean('is_login_allowed')->default(true);
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasTable('dobs_user') && $schema->hasColumn('dobs_user', 'is_login_allowed')) {
            $schema->table('dobs_user', function (Blueprint $table) {
                $table->dropColumn('is_login_allowed');
            });
        }
    }
};
