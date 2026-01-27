<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            basic_fields($table, 'orders');
            $table->string('reference')->unique();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->foreignId('customer_id')->constrained((new (get_user_model()))->getTable());
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->decimal('packaging_cost', 12, 2)->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('shipping_address_id')->nullable()->constrained('shipping_addresses');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->foreignId('coupon_id')->nullable()->constrained('coupons');
            $table->text('notes')->nullable();
            // metadata added by basic_fields()
        });

        Schema::create('order_items', function (Blueprint $table) {
            basic_fields($table, 'order_items');
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->string('currency', 3)->nullable();
            $table->json('snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};


