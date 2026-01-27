<?php

namespace Lyre\Commerce\Models;

use Lyre\File\Concerns\HasFile;
use Lyre\Model;

class ProductVariant extends Model
{
    use HasFile;

    const ID_COLUMN = 'slug';

    protected $fillable = [
        'product_id',
        'name',
        'enabled',
        'attributes',
        'barcode',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    protected $with = ['files'];

    protected array $included = ['price', 'compare_at_price', 'currency', 'featured_image'];

    protected array $excluded = ['userProductVariants'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function userProductVariants()
    {
        return $this->hasMany(UserProductVariant::class);
    }

    public function getPriceAttribute()
    {
        if (!$this->relationLoaded('userProductVariants')) {
            $this->load('userProductVariants.prices');
        }

        $userVariant = $this->userProductVariants->first();
        if (!$userVariant) {
            return null;
        }

        if (!$userVariant->relationLoaded('prices')) {
            $userVariant->load('prices');
        }

        $price = $userVariant->prices->first();
        return $price?->price ?? null;
    }

    public function getFeaturedImageAttribute()
    {
        // Check variant images first
        if ($this->relationLoaded('files')) {
            $variantImage = $this->files()->where('mimetype', 'like', 'image/%')->first();
            if ($variantImage) {
                return \Lyre\File\Http\Resources\File::make($variantImage);
            }
        }

        // Fallback to product image
        if (!$this->relationLoaded('product')) {
            $this->load('product.files');
        }

        return $this->product->featured_image ?? null;
    }

    public function getCompareAtPriceAttribute()
    {
        if (!$this->relationLoaded('userProductVariants')) {
            $this->load('userProductVariants.prices');
        }

        $userVariant = $this->userProductVariants->first();
        if (!$userVariant) {
            return null;
        }

        if (!$userVariant->relationLoaded('prices')) {
            $userVariant->load('prices');
        }

        $price = $userVariant->prices->first();
        return $price?->compare_at_price ?? null;
    }

    public function getCurrencyAttribute()
    {
        if (!$this->relationLoaded('userProductVariants')) {
            $this->load('userProductVariants.prices');
        }

        $userVariant = $this->userProductVariants->first();
        if (!$userVariant) {
            return config('commerce.default_currency', 'KES');
        }

        if (!$userVariant->relationLoaded('prices')) {
            $userVariant->load('prices');
        }

        $price = $userVariant->prices->first();
        return $price?->currency ?? config('commerce.default_currency', 'KES');
    }
}
