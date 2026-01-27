<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_product_variants', function (Blueprint $table) {
            basic_fields($table, 'user_product_variants');
            $table->foreignId('user_id')->constrained((new (get_user_model()))->getTable());
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->integer('stock_level')->default(0);
            $table->integer('min_qty')->nullable();
            $table->integer('max_qty')->nullable();
        });

        Schema::create('product_variant_prices', function (Blueprint $table) {
            basic_fields($table, 'product_variant_prices');
            $table->foreignId('user_product_variant_id')->constrained('user_product_variants')->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->string('currency', 3);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->boolean('tax_included')->default(false);
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_through')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_prices');
        Schema::dropIfExists('user_product_variants');
    }
};


