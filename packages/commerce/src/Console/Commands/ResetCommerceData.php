<?php

namespace Lyre\Commerce\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lyre\Commerce\Models\Product;
use Lyre\Commerce\Models\ProductVariant;
use Lyre\Commerce\Models\UserProductVariant;
use Lyre\Commerce\Models\ProductVariantPrice;
use Lyre\Commerce\Models\Order;
use Lyre\Commerce\Models\OrderItem;
use Lyre\Commerce\Models\Coupon;
use Lyre\Commerce\Models\CouponUsage;
use Lyre\Facet\Models\Facet;
use Lyre\Facet\Models\FacetValue;

class ResetCommerceData extends Command
{
    protected $signature = 'commerce:reset';
    protected $description = 'Clear all commerce data (products, variants, facets, orders, etc.)';

    public function handle()
    {
        $this->info('Starting commerce data reset...');

        // Disable foreign key checks temporarily (MySQL only)
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        try {
            // Clear commerce data
            $this->info('Clearing orders and order items...');
            OrderItem::query()->delete();
            Order::query()->delete();

            $this->info('Clearing coupon usages and coupons...');
            CouponUsage::query()->delete();
            Coupon::query()->delete();

            $this->info('Clearing product variant prices and user product variants...');
            ProductVariantPrice::query()->delete();
            UserProductVariant::query()->delete();

            $this->info('Clearing product variants...');
            ProductVariant::query()->delete();

            $this->info('Clearing products...');
            Product::query()->delete();

            $this->info('Clearing facet values and facets...');
            // Clear facet values that are related to commerce
            $commerceFacets = Facet::whereIn('slug', ['category', 'brand', 'collection'])->get();
            foreach ($commerceFacets as $facet) {
                FacetValue::where('facet_id', $facet->id)->delete();
            }
            Facet::whereIn('slug', ['category', 'brand', 'collection'])->delete();

            $this->info('✅ Commerce data cleared successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to clear commerce data: ' . $e->getMessage());
            return 1;
        } finally {
            // Re-enable foreign key checks (MySQL only)
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }

        return 0;
    }
}

