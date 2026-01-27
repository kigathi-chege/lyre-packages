<?php

namespace Lyre\Commerce\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Lyre\Commerce\Models\Product;
use Lyre\Commerce\Models\ProductVariant;
use Lyre\Commerce\Models\UserProductVariant;
use Lyre\Commerce\Models\ProductVariantPrice;
use Lyre\Commerce\Models\Location;
use Lyre\Commerce\Models\Coupon;
use Lyre\Facet\Models\Facet;
use Lyre\Facet\Models\FacetValue;
use Lyre\File\Repositories\Contracts\FileRepositoryInterface;

class CommerceComprehensiveSeeder extends Seeder
{
    private $userModel;
    private $user;

    private array $categories = [];
    private array $brands = [];
    private array $locations = [];
    private array $products = [];
    private array $imageFiles = [];

    private array $categoryHierarchy = [
        'Accessories' => [
            'Bags' => ['Backpacks', 'Handbags', 'Wallets'],
            'Belts',
            'Sunglasses',
            'Watches',
            'Jewelry',
        ],
        'Footwear' => [
            'Sneakers',
            'Shoes' => ['Boots', 'Loafers', 'Sandals'],
        ],
        'Clothing' => [
            'T-Shirts',
            'Jeans',
            'Dresses',
            'Jackets',
            'Shorts',
        ],
        'Electronics' => [
            'Computing Devices' => ['Laptops', 'Tablets'],
            'Mobile Devices' => ['Phones'],
            'Audio' => ['Headphones'],
            'Imaging' => ['Cameras'],
        ],
    ];

    public function run(): void
    {
        $this->command->info('🚀 Starting Commerce Comprehensive Seeder');

        $this->userModel = get_user_model();
        $this->user = $this->userModel::first();

        if (!$this->user) {
            $this->command->warn('No user found. Creating demo merchant...');
            $this->user = $this->userModel::create([
                'name' => 'Demo Merchant',
                'email' => 'merchant@demo.com',
                'password' => bcrypt('password'),
            ]);
        }

        $this->loadImageFiles();
        $this->createFacetsAndCategories();
        $this->createLocations();
        $this->createCoupons();
        $this->createProducts();
        $this->createUserProductVariants();

        $this->command->info('✅ Commerce seeding completed successfully');
        $this->command->info('Products created: ' . count($this->products));
    }

    private function loadImageFiles(): void
    {
        $this->command->info('🖼️  Loading image files...');

        $picturesPath = "/media/user/Chege/HP 830 G6/Pictures/Galleries/Tate Gallery London - A Selection (Art Paintings)";

        if (!is_dir($picturesPath)) {
            $this->command->warn("Images directory not found: {$picturesPath}");
            return;
        }

        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = glob($picturesPath . '/*.{' . implode(',', $extensions) . '}', GLOB_BRACE);

        $bar = $this->command->getOutput()->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            if (is_file($file) && filesize($file) <= 10 * 1024 * 1024) {
                $this->imageFiles[] = $file;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Images loaded: ' . count($this->imageFiles));
    }

    private function createFacetsAndCategories(): void
    {
        $this->command->info('🧩 Creating facets & category hierarchy...');

        $categoryFacet = Facet::firstOrCreate(['slug' => 'category'], ['name' => 'Category']);
        $brandFacet = Facet::firstOrCreate(['slug' => 'brand'], ['name' => 'Brand']);
        $collectionFacet = Facet::firstOrCreate(['slug' => 'collection'], ['name' => 'Collection']);

        $this->createFacetHierarchy($categoryFacet, $this->categoryHierarchy);

        foreach ($this->getBrands() as $brand) {
            $this->brands[$brand] = FacetValue::firstOrCreate(
                ['facet_id' => $brandFacet->id, 'slug' => Str::slug($brand)],
                ['name' => $brand]
            )->id;
        }

        foreach ($this->getCollections() as $name => $description) {
            FacetValue::firstOrCreate(
                ['facet_id' => $collectionFacet->id, 'slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description]
            );
        }

        $this->command->info('Categories created: ' . count($this->categories));
        $this->command->info('Brands created: ' . count($this->brands));
    }

    private function createFacetHierarchy(Facet $facet, array $tree, ?FacetValue $parent = null): void
    {
        foreach ($tree as $key => $value) {
            $name = is_string($key) ? $key : $value;
            $children = is_array($value) ? $value : null;

            $facetValue = FacetValue::firstOrCreate(
                [
                    'facet_id' => $facet->id,
                    'slug' => Str::slug($name),
                    'parent_id' => $parent?->id,
                ],
                ['name' => $name]
            );

            if (!$children) {
                $this->categories[$name] = $facetValue->id;
            }

            if ($children) {
                $this->createFacetHierarchy($facet, $children, $facetValue);
            }
        }
    }

    private function createProducts(): void
    {
        $this->command->info('📦 Creating products...');

        $names = [
            'Leather Backpack',
            'Running Sneakers',
            'Classic T-Shirt',
            'Wireless Headphones',
            'Smartphone',
            'Laptop',
            'Camera',
        ];

        $brands = array_keys($this->brands);
        $categories = array_keys($this->categories);

        $target = 500;
        $bar = $this->command->getOutput()->createProgressBar($target);
        $bar->start();

        while (count($this->products) < $target) {
            foreach ($names as $base) {
                if (count($this->products) >= $target) break;

                $brand = $brands[array_rand($brands)];
                $category = $categories[array_rand($categories)];

                $product = Product::create([
                    'name' => $base . ' ' . Str::random(4),
                    'slug' => Str::slug($base . '-' . Str::random(8)),
                    'description' => 'High quality ' . strtolower($base),
                    'saleable' => true,
                    'status' => 'active',
                    'metadata' => ['brand' => $brand],
                ]);

                if ($this->imageFiles) {
                    $path = $this->imageFiles[array_rand($this->imageFiles)];
                    $file = new UploadedFile($path, basename($path), mime_content_type($path), null, true);
                    $fileRecord = app(FileRepositoryInterface::class)->uploadFile($file, $product->slug);
                    $product->attachFile($fileRecord->id);
                }

                $collection = FacetValue::whereHas('facet', fn ($q) =>
                    $q->where('slug', 'collection')
                )->inRandomOrder()->first();

                $product->attachFacetValues([
                    $this->categories[$category],
                    $this->brands[$brand],
                    $collection->id,
                ]);

                $this->products[] = $product;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->newLine();
    }

    private function createUserProductVariants(): void
    {
        $this->command->info('🔧 Creating variants & pricing...');

        $bar = $this->command->getOutput()->createProgressBar(count($this->products));
        $bar->start();

        foreach ($this->products as $product) {
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'name' => 'Standard',
                'enabled' => true,
            ]);

            $userVariant = UserProductVariant::create([
                'user_id' => $this->user->id,
                'product_variant_id' => $variant->id,
                'sku' => 'SKU-' . strtoupper(Str::random(8)),
                'stock_level' => rand(10, 200),
            ]);

            ProductVariantPrice::create([
                'user_product_variant_id' => $userVariant->id,
                'price' => rand(1000, 10000) / 100,
                'currency' => 'KES',
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
    }

    private function getBrands(): array
    {
        return ['Nike', 'Adidas', 'Apple', 'Samsung', 'Sony', 'Puma', 'Gucci'];
    }

    private function getCollections(): array
    {
        return [
            'Featured' => 'Highlighted products',
            'Best Sellers' => 'Top selling items',
            'New Arrivals' => 'Latest additions',
            'On Sale' => 'Discounted items',
        ];
    }

       private function createLocations(): void
    {
        $locations = [
            ['name' => 'Nairobi CBD', 'latitude' => -1.2921, 'longitude' => 36.8219, 'address' => 'Nairobi Central Business District', 'delivery_fee' => 100.00],
            ['name' => 'Westlands', 'latitude' => -1.2644, 'longitude' => 36.8031, 'address' => 'Westlands, Nairobi', 'delivery_fee' => 120.00],
            ['name' => 'Kileleshwa', 'latitude' => -1.2944, 'longitude' => 36.7819, 'address' => 'Kileleshwa, Nairobi', 'delivery_fee' => 150.00],
            ['name' => 'Parklands', 'latitude' => -1.2564, 'longitude' => 36.8011, 'address' => 'Parklands, Nairobi', 'delivery_fee' => 130.00],
            ['name' => 'Lavington', 'latitude' => -1.2833, 'longitude' => 36.7667, 'address' => 'Lavington, Nairobi', 'delivery_fee' => 140.00],
        ];

        foreach ($locations as $locationData) {
            $location = Location::firstOrCreate(
                ['slug' => Str::slug($locationData['name'])],
                $locationData
            );
            $this->locations[] = $location;
        }
    }

    private function createCoupons(): void
    {
        $coupons = [
            ['code' => 'WELCOME10', 'discount' => 10, 'discount_type' => 'percent', 'status' => 'active', 'usage_limit' => 1000, 'minimum_amount' => 50],
            ['code' => 'SAVE20', 'discount' => 20, 'discount_type' => 'percent', 'status' => 'active', 'usage_limit' => 500, 'minimum_amount' => 100],
            ['code' => 'FLAT50', 'discount' => 50, 'discount_type' => 'fixed', 'status' => 'active', 'usage_limit' => 200, 'minimum_amount' => 200],
            ['code' => 'NEWUSER', 'discount' => 15, 'discount_type' => 'percent', 'status' => 'active', 'usage_limit' => 1000, 'minimum_amount' => 25],
        ];

        foreach ($coupons as $couponData) {
            Coupon::firstOrCreate(['code' => $couponData['code']], $couponData);
        }
    }
}
