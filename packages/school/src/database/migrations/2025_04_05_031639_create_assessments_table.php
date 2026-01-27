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
        $prefix = config('lyre.table_prefix');
        $tableName = $prefix . 'assessments';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName) {
                basic_fields($table, $tableName);

                $table->string('name');
                $table->timestamp('published_at')->nullable();

                $userTable = config('auth.providers.users.table', 'users');
                $table->foreignId('creator_id')
                    ->nullable()
                    ->constrained($userTable)
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('lyre.table_prefix');
        $tableName = $prefix . 'assessments';

        Schema::dropIfExists($tableName);
    }
};
