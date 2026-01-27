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
        $tableName = $prefix . 'guests';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName, $prefix) {
                $table->id();
                $table->timestamps();

                $table->string('ip')->nullable();
                $table->string('user_agent')->nullable();
                $table->string('referrer')->nullable();
                $table->string('current_url')->nullable();
                $table->string('previous_url')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->string('language')->nullable();
                $table->string('session_id')->nullable();

                $table->uuid('uuid')->unique()->nullable();
                $table->foreignId('user_id')->nullable()->constrained();
            });
        }

        if (!Schema::hasColumn('guests', 'currency_code')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->string('currency_code')->nullable();
                $table->string('country_code')->nullable();
                $table->string('region_code')->nullable();
                $table->string('region_name')->nullable();
                $table->string('zip_code')->nullable();
                $table->string('iso_code')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('metro_code')->nullable();
                $table->string('area_code')->nullable();
                $table->string('timezone')->nullable();
            });
        }

        if (Schema::hasColumn('guests', 'user_agent')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->text('user_agent')->nullable()->change();
                $table->text('referrer')->nullable()->change();
                $table->text('current_url')->nullable()->change();
            });
        }

        if (!Schema::hasColumn('users', 'is_guest')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_guest')->default(false);
            });
        }

        if (Schema::hasColumn('guests', 'previous_url')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->text('previous_url')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
