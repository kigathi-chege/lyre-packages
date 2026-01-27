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
        $tableName = $prefix . 'task_answers';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($prefix, $tableName) {
                basic_fields($table, $tableName);

                $table->string('name');
                $table->boolean('is_correct')->default(false);
                $table->integer('score')->default(0);

                $table->foreignId('task_id')
                    ->constrained($prefix . 'tasks')
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
        $tableName = $prefix . 'task_answers';

        Schema::dropIfExists($tableName);
    }
};
