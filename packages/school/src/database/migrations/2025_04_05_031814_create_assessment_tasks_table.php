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
        $tableName = $prefix . 'assessment_tasks';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($prefix, $tableName) {
                basic_fields($table, $tableName);

                $table->foreignId('assessment_id')
                    ->constrained($prefix . 'assessments')
                    ->onDelete('cascade');

                $table->foreignId('task_id')
                    ->constrained($prefix . 'tasks')
                    ->onDelete('cascade');

                $table->integer('order')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('lyre.table_prefix');
        $tableName = $prefix . 'assessment_tasks';

        Schema::dropIfExists($tableName);
    }
};
