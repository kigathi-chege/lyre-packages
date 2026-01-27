<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            basic_fields($table, 'coupons');
            $table->string('code')->unique();
            $table->decimal('discount', 12, 2);
            $table->string('discount_type'); // percent|fixed
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('status')->default('active');
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->decimal('minimum_amount', 12, 2)->nullable();
            $table->json('applies_to')->nullable();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            basic_fields($table, 'coupon_usages');
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained((new (get_user_model()))->getTable());
            $table->decimal('amount_saved', 12, 2)->default(0);
            $table->timestamp('used_at')->nullable();
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};


