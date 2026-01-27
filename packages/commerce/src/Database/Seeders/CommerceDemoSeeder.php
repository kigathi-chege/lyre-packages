<?php

namespace Lyre\Commerce\Database\Seeders;

use Illuminate\Database\Seeder;
use Lyre\Commerce\Models\Product;
use Lyre\Commerce\Models\ProductVariant;
use Lyre\Commerce\Models\UserProductVariant;
use Lyre\Commerce\Models\ProductVariantPrice;
use Lyre\Commerce\Models\Location;
use Lyre\Commerce\Models\Coupon;

class CommerceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $userModel = get_user_model();
        $user = $userModel::first();
        
        if (!$user) {
            $this->command->warn('No user found. Create a user first.');
            return;
        }

        // Create Location
        $location = Location::create([
            'name' => 'Nairobi CBD',
            'latitude' => -1.2921,
            'longitude' => 36.8219,
            'address' => 'Nairobi Central Business District',
            'delivery_fee' => 100.00,
        ]);

        // Create Product
        $product = Product::create([
            'name' => 'Demo Product',
            'saleable' => true,
            'hscode' => '123456',
            'hstype' => 'Type A',
            'hsdescription' => 'Demo product description',
            'status' => 'active',
        ]);

        // Create Product Variant
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard Variant',
            'enabled' => true,
            'attributes' => ['size' => 'Medium', 'color' => 'Blue'],
        ]);

        // Create User Product Variant
        $userVariant = UserProductVariant::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'sku' => 'DEMO-' . strtoupper(uniqid()),
            'stock_level' => 100,
            'min_qty' => 1,
            'max_qty' => 10,
        ]);

        // Create Price
        ProductVariantPrice::create([
            'user_product_variant_id' => $userVariant->id,
            'price' => 500.00,
            'currency' => config('commerce.default_currency', 'USD'),
            'compare_at_price' => 600.00,
            'tax_included' => false,
        ]);

        // Create Coupon
        Coupon::create([
            'code' => 'DEMO10',
            'discount' => 10,
            'discount_type' => 'percent',
            'status' => 'active',
            'usage_limit' => 100,
            'minimum_amount' => 100.00,
        ]);

        $this->command->info('Commerce demo data seeded successfully!');
        $this->command->info("Product ID: {$product->id}");
        $this->command->info("Variant ID: {$variant->id}");
        $this->command->info("User Variant ID: {$userVariant->id}");
        $this->command->info("Location ID: {$location->id}");
        $this->command->info("Coupon Code: DEMO10");
    }
}

