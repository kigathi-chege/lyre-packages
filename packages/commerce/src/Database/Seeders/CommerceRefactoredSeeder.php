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

class CommerceRefactoredSeeder extends Seeder
{
    private $userModel;
    private $user;
    private $categories = [];
    private $brands = [];
    private $locations = [];
    private $products = [];
    private $imageFiles = [];

    public function run(): void
    {
        $this->userModel = get_user_model();
        $this->user = $this->userModel::first();

        if (!$this->user) {
            $this->command->warn('No user found. Creating a user first...');
            $this->user = $this->userModel::create([
                'name' => 'Demo Merchant',
                'email' => 'merchant@demo.com',
                'password' => bcrypt('password'),
            ]);
        }

        $this->command->info('Loading images from Pictures folder...');
        $this->loadImageFiles();

        $this->command->info('Creating Facets and Categories...');
        $this->createFacetsAndCategories();

        $this->command->info('Creating Locations...');
        $this->createLocations();

        $this->command->info('Creating Coupons...');
        $this->createCoupons();

        $this->command->info('Creating Products with 3+ Variants each...');
        $this->createProducts();

        $this->command->info('Creating User Product Variants and Prices...');
        $this->createUserProductVariants();

        $this->command->info('✅ Commerce seeding completed!');
        $this->command->info("Created " . count($this->products) . " products");
        $this->command->info("Total variants: " . ProductVariant::count());
    }

    private function loadImageFiles(): void
    {
        $picturesPath = env('HOME', '~') . '/Pictures';
        if (!is_dir($picturesPath)) {
            $this->command->warn("Pictures directory not found at {$picturesPath}");
            return;
        }

        $supportedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = glob($picturesPath . '/*.{' . implode(',', $supportedExtensions) . '}', GLOB_BRACE);
        
        foreach ($files as $file) {
            if (is_file($file) && filesize($file) > 0) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $supportedExtensions)) {
                    $this->imageFiles[] = $file;
                }
            }
        }
        
        $this->command->info("Loaded " . count($this->imageFiles) . " image files");
    }

    private function createFacetsAndCategories(): void
    {
        // Create Category Facet
        $categoryFacet = Facet::firstOrCreate(
            ['slug' => 'category'],
            ['name' => 'Category', 'description' => 'Product Categories']
        );

        // Create Brand Facet
        $brandFacet = Facet::firstOrCreate(
            ['slug' => 'brand'],
            ['name' => 'Brand', 'description' => 'Product Brands']
        );

        // Create Collection Facet (for merchandising tags)
        $collectionFacet = Facet::firstOrCreate(
            ['slug' => 'collection'],
            ['name' => 'Collection', 'description' => 'Product Collections']
        );

        // Categories
        $categories = [
            'Bags', 'Sneakers', 'Belts', 'Sunglasses', 'Electronics',
            'Clothing', 'Accessories', 'Footwear', 'Watches', 'Jewelry',
            'Laptops', 'Phones', 'Tablets', 'Headphones', 'Cameras'
        ];

        foreach ($categories as $categoryName) {
            $slug = Str::slug($categoryName);
            $facetValue = FacetValue::firstOrCreate(
                ['slug' => $slug, 'facet_id' => $categoryFacet->id],
                ['name' => $categoryName, 'description' => "{$categoryName} products"]
            );
            $this->categories[$categoryName] = $facetValue->id;
        }

        // Brands
        $brands = [
            'Nike', 'Adidas', 'Puma', 'Samsung', 'Apple', 'Sony',
            'Canon', 'Dell', 'HP', 'Lenovo', 'Gucci', 'Prada',
            'Ray-Ban', 'Oakley', 'Michael Kors', 'Coach'
        ];

        foreach ($brands as $brandName) {
            $slug = Str::slug($brandName);
            $facetValue = FacetValue::firstOrCreate(
                ['slug' => $slug, 'facet_id' => $brandFacet->id],
                ['name' => $brandName, 'description' => "{$brandName} brand products"]
            );
            $this->brands[$brandName] = $facetValue->id;
        }

        // Collections (merchandising tags)
        $collections = [
            'Best Sellers' => 'Our most popular products',
            'Latest' => 'Newly added products',
            'Featured' => 'Handpicked products we recommend',
            'On Sale' => 'Special offers and discounted items',
            'New Arrivals' => 'Recently added products',
            'Most Viewed' => 'Products with the most views'
        ];

        foreach ($collections as $collectionName => $description) {
            $slug = Str::slug($collectionName);
            FacetValue::firstOrCreate(
                ['slug' => $slug, 'facet_id' => $collectionFacet->id],
                ['name' => $collectionName, 'description' => $description]
            );
        }
    }

    private function createLocations(): void
    {
        $locations = [
            ['name' => 'Nairobi', 'latitude' => -1.2921, 'longitude' => 36.8219, 'address' => 'Nairobi CBD', 'delivery_fee' => 200],
            ['name' => 'Mombasa', 'latitude' => -4.0435, 'longitude' => 39.6682, 'address' => 'Mombasa Town', 'delivery_fee' => 300],
            ['name' => 'Kisumu', 'latitude' => -0.0917, 'longitude' => 34.7680, 'address' => 'Kisumu City', 'delivery_fee' => 250],
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
            [
                'code' => 'WELCOME10',
                'discount' => 10,
                'discount_type' => 'percentage',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(365),
                'status' => 'active',
                'usage_limit' => 1000,
                'minimum_amount' => 1000,
            ],
            [
                'code' => 'SAVE50',
                'discount' => 50,
                'discount_type' => 'fixed',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(365),
                'status' => 'active',
                'usage_limit' => 100,
                'minimum_amount' => 5000,
            ],
        ];

        foreach ($coupons as $couponData) {
            Coupon::firstOrCreate(['code' => $couponData['code']], $couponData);
        }
    }

    private function createProducts(): void
    {
        $productTemplates = [
            // Electronics
            ['name' => 'Smartphone', 'category' => 'Phones', 'metadata' => ['type' => 'Mobile', 'connectivity' => '5G']],
            ['name' => 'Laptop', 'category' => 'Laptops', 'metadata' => ['type' => 'Computer', 'processor' => 'Intel']],
            ['name' => 'Headphones', 'category' => 'Headphones', 'metadata' => ['type' => 'Audio', 'wireless' => true]],
            ['name' => 'Camera', 'category' => 'Cameras', 'metadata' => ['type' => 'Photography', 'resolution' => '4K']],
            ['name' => 'Tablet', 'category' => 'Tablets', 'metadata' => ['type' => 'Mobile', 'screen_size' => '10 inch']],
            
            // Fashion
            ['name' => 'Backpack', 'category' => 'Bags', 'metadata' => ['material' => 'Leather', 'style' => 'Casual']],
            ['name' => 'Running Shoes', 'category' => 'Sneakers', 'metadata' => ['material' => 'Mesh', 'activity' => 'Running']],
            ['name' => 'Leather Belt', 'category' => 'Belts', 'metadata' => ['material' => 'Leather', 'style' => 'Formal']],
            ['name' => 'Sunglasses', 'category' => 'Sunglasses', 'metadata' => ['material' => 'Plastic', 'protection' => 'UV400']],
            ['name' => 'Watch', 'category' => 'Watches', 'metadata' => ['material' => 'Stainless Steel', 'type' => 'Smartwatch']],
            
            // More products
            ['name' => 'T-Shirt', 'category' => 'Clothing', 'metadata' => ['material' => 'Cotton', 'style' => 'Casual']],
            ['name' => 'Jeans', 'category' => 'Clothing', 'metadata' => ['material' => 'Denim', 'fit' => 'Regular']],
            ['name' => 'Wallet', 'category' => 'Accessories', 'metadata' => ['material' => 'Leather', 'style' => 'Bifold']],
            ['name' => 'Necklace', 'category' => 'Jewelry', 'metadata' => ['material' => 'Gold', 'style' => 'Chain']],
            ['name' => 'Boots', 'category' => 'Footwear', 'metadata' => ['material' => 'Leather', 'style' => 'Combat']],
        ];

        $variantsPerProduct = [
            // Variant specifications: [name, attributes, price_multiplier]
            [
                ['name' => 'Standard', 'attributes' => ['color' => 'Black', 'size' => 'M'], 'price_multiplier' => 1.0],
                ['name' => 'Premium', 'attributes' => ['color' => 'Blue', 'size' => 'L'], 'price_multiplier' => 1.2],
                ['name' => 'Deluxe', 'attributes' => ['color' => 'Red', 'size' => 'XL'], 'price_multiplier' => 1.5],
            ],
            [
                ['name' => 'Small', 'attributes' => ['color' => 'Silver', 'storage' => '128GB'], 'price_multiplier' => 1.0],
                ['name' => 'Medium', 'attributes' => ['color' => 'Gold', 'storage' => '256GB'], 'price_multiplier' => 1.3],
                ['name' => 'Large', 'attributes' => ['color' => 'Black', 'storage' => '512GB'], 'price_multiplier' => 1.6],
            ],
            [
                ['name' => 'Basic', 'attributes' => ['color' => 'White', 'weight' => '250g'], 'price_multiplier' => 1.0],
                ['name' => 'Advanced', 'attributes' => ['color' => 'Black', 'weight' => '300g'], 'price_multiplier' => 1.25],
                ['name' => 'Pro', 'attributes' => ['color' => 'Red', 'weight' => '350g'], 'price_multiplier' => 1.5],
            ],
        ];

        $basePrice = 1000;
        $productCount = 0;

        foreach ($productTemplates as $template) {
            $productName = $template['name'];
            $category = $template['category'];
            $brand = array_rand($this->brands);
            $brandId = $this->brands[$brand];

            // Get random variant template
            $variantTemplate = $variantsPerProduct[array_rand($variantsPerProduct)];

            $productSlug = Str::slug($productName . '-' . $brand);
            $product = Product::create([
                'slug' => $productSlug,
                'name' => $productName . ' - ' . $brand,
                'description' => "High-quality {$productName} from {$brand}. Perfect for everyday use.",
                'saleable' => true,
                'hscode' => str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'hstype' => 'General',
                'hsdescription' => $productName,
                'status' => 'active',
                'metadata' => array_merge($template['metadata'], ['brand' => $brand]),
            ]);

            // Attach product image (skip for now to avoid memory issues, can be added manually)
            // Products will have featured_image from product files if needed
            // if (!empty($this->imageFiles)) {
            //     $randomImagePath = $this->imageFiles[array_rand($this->imageFiles)];
            //     try {
            //         $mimeType = mime_content_type($randomImagePath);
            //         $fileSize = filesize($randomImagePath);
            //         
            //         // Skip AVIF and large images (> 5MB) to prevent memory issues
            //         if (strpos($mimeType, 'image/avif') === false && $fileSize <= 5 * 1024 * 1024) {
            //             $file = new UploadedFile(
            //                 $randomImagePath,
            //                 basename($randomImagePath),
            //                 $mimeType,
            //                 null,
            //                 true
            //             );
            //             $uniqueFileName = $productName . '-' . $productCount . '-' . time() . '-' . Str::random(6);
            //             $fileRecord = app(FileRepositoryInterface::class)->uploadFile($file, $uniqueFileName);
            //             $product->attachFile($fileRecord->id);
            //             unset($file, $fileRecord);
            //         }
            //     } catch (\Exception $e) {
            //         $this->command->warn("Failed to attach image to product {$productName}: " . $e->getMessage());
            //     }
            // }

            // Attach facets
            $product->attachFacetValues([
                $this->categories[$category],
                $brandId,
            ]);

            // Add random collection (30% chance)
            if (rand(1, 100) <= 30) {
                $collectionFacet = Facet::where('slug', 'collection')->first();
                $collections = ['Best Sellers', 'Latest', 'Featured', 'On Sale', 'New Arrivals', 'Most Viewed'];
                $randomCollection = $collections[array_rand($collections)];
                $collectionValue = FacetValue::where('facet_id', $collectionFacet->id)
                    ->where('slug', Str::slug($randomCollection))
                    ->first();
                if ($collectionValue) {
                    $product->attachFacetValues([$collectionValue->id]);
                }
            }

            // Create AT LEAST 3 variants per product
            $numVariants = rand(3, 5); // 3-5 variants per product
            for ($i = 0; $i < $numVariants; $i++) {
                $variantSpec = $variantTemplate[$i % count($variantTemplate)];
                $variantName = $variantSpec['name'] . ' ' . ($i + 1);
                
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $variantName,
                    'enabled' => true,
                    'attributes' => $variantSpec['attributes'],
                    'barcode' => 'BC' . str_pad((string)rand(100000, 999999), 10, '0', STR_PAD_LEFT),
                ]);

                // Skip variant images for now to avoid memory issues
                // Variants can use product images or be added manually via Filament
                // if ($i === 0 && !empty($this->imageFiles) && rand(1, 100) <= 30) {
                //     $randomImagePath = $this->imageFiles[array_rand($this->imageFiles)];
                //     try {
                //         $mimeType = mime_content_type($randomImagePath);
                //         $fileSize = filesize($randomImagePath);
                //         
                //         // Skip AVIF and large images (> 5MB)
                //         if (strpos($mimeType, 'image/avif') === false && $fileSize <= 5 * 1024 * 1024) {
                //             $file = new UploadedFile(
                //                 $randomImagePath,
                //                 basename($randomImagePath),
                //                 $mimeType,
                //                 null,
                //                 true
                //             );
                //             $uniqueFileName = $productName . '-variant-' . $i . '-' . time() . '-' . Str::random(6);
                //             $fileRecord = app(FileRepositoryInterface::class)->uploadFile($file, $uniqueFileName);
                //             $variant->attachFile($fileRecord->id);
                //             unset($file, $fileRecord);
                //         }
                //     } catch (\Exception $e) {
                //         // Silent fail for variant images
                //     }
                // }
            }

            $this->products[] = $product;
            $productCount++;

            // Free memory every 5 products
            if ($productCount % 5 === 0) {
                gc_collect_cycles();
                $this->command->info("Created {$productCount} products...");
            }
        }

        $this->command->info("Created {$productCount} products");
    }

    private function createUserProductVariants(): void
    {
        $this->command->info('Creating variants and pricing...');

        $variants = ProductVariant::with('product')->get();
        $progressBar = $this->command->getOutput()->createProgressBar($variants->count());
        $progressBar->start();

        foreach ($variants as $variant) {
            $metadata = $variant->product->metadata ?? [];
            $basePrice = rand(500, 5000);
            
            // Calculate price based on variant attributes
            $priceMultiplier = 1.0;
            if (isset($variant->attributes['storage'])) {
                if ($variant->attributes['storage'] === '256GB') $priceMultiplier = 1.3;
                if ($variant->attributes['storage'] === '512GB') $priceMultiplier = 1.6;
            }
            if (isset($variant->attributes['size'])) {
                if ($variant->attributes['size'] === 'L') $priceMultiplier = 1.2;
                if ($variant->attributes['size'] === 'XL') $priceMultiplier = 1.5;
            }

            $userVariant = UserProductVariant::create([
                'user_id' => $this->user->id,
                'product_variant_id' => $variant->id,
                'sku' => 'SKU-' . strtoupper(Str::random(8)),
                'stock_level' => rand(10, 100),
                'min_qty' => 1,
                'max_qty' => 10,
            ]);

            ProductVariantPrice::create([
                'user_product_variant_id' => $userVariant->id,
                'price' => round($basePrice * $priceMultiplier),
                'compare_at_price' => rand(0, 100) <= 30 ? round($basePrice * $priceMultiplier * 1.2) : null,
                'currency' => config('commerce.default_currency', 'KES'),
            ]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }
}

