<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            basic_fields($table, 'products');
            $table->string('name');
            $table->boolean('saleable')->default(true);
            $table->string('hscode')->nullable();
            $table->string('hstype')->nullable();
            $table->text('hsdescription')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            basic_fields($table, 'product_variants');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('enabled')->default(true);
            $table->json('attributes')->nullable();
            $table->string('barcode')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};


