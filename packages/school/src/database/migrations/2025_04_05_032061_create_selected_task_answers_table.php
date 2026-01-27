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
        $tableName = $prefix . 'selected_task_answers';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($prefix, $tableName) {
                basic_fields($table, $tableName);

                $table->foreignId('task_id')
                    ->nullable()
                    ->constrained($prefix . 'tasks')
                    ->onDelete('cascade');

                $table->foreignId('assessment_id')
                    ->nullable()
                    ->constrained($prefix . 'assessments')
                    ->onDelete('cascade');

                $userTable = config('auth.providers.users.table', 'users');
                $table->foreignId('user_id')
                    ->constrained($userTable)
                    ->onDelete('cascade');

                $table->foreignId('assessment_task_id')
                    ->nullable()
                    ->constrained($prefix . 'assessment_tasks')
                    ->onDelete('cascade');

                $table->foreignId('task_answer_id')
                    ->nullable()
                    ->constrained($prefix . 'task_answers')
                    ->onDelete('cascade');

                $table->foreignId('assessment_attempt_id')
                    ->nullable()
                    ->constrained($prefix . 'assessment_attempts')
                    ->onDelete('cascade');

                $table->unique(
                    ['user_id', 'assessment_task_id', 'task_answer_id', 'assessment_attempt_id'],
                    'unique_user_assessment_answer'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('lyre.table_prefix');
        $tableName = $prefix . 'selected_task_answers';

        Schema::dropIfExists($tableName);
    }
};
