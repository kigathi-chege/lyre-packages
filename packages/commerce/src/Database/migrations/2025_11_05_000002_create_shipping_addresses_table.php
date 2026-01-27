<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipping_addresses', function (Blueprint $table) {
            basic_fields($table, 'shipping_addresses');
            $table->foreignId('user_id')->constrained((new (get_user_model()))->getTable());
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->string('delivery_method')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('is_default')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};


