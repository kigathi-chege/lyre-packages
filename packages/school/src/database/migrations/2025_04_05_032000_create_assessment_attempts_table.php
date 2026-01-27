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
        $tableName = $prefix . 'assessment_attempts';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($prefix, $tableName) {
                basic_fields($table, $tableName);

                $table->string('mode')->nullable();

                $table->foreignId('assessment_id')
                    ->constrained($prefix . 'assessments')
                    ->onDelete('cascade');

                $userTable = config('auth.providers.users.table', 'users');
                $table->foreignId('user_id')
                    ->constrained($userTable)
                    ->onDelete('cascade');

                $table->integer('score')->nullable();
                $table->enum('status', ['in_progress', 'completed'])->default('in_progress')
                    ->comment('in_progress: The attempt is ongoing, completed: The attempt was completed successfully');
                $table->timestamp('completed_at')->nullable();
                $table->json('task_order')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('lyre.table_prefix');
        $tableName = $prefix . 'assessment_attempts';

        Schema::dropIfExists($tableName);
    }
};
