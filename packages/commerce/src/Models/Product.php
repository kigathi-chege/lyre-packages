<?php

namespace Lyre\Commerce\Models;

use Lyre\Content\Concerns\HasInteractions;
use Lyre\Facet\Concerns\HasFacet;
use Lyre\File\Concerns\HasFile;
use Lyre\Model;

class Product extends Model
{
    use HasFacet, HasFile, HasInteractions;

    const ID_COLUMN = 'slug';

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $with = ['facetValues', 'files', 'interactions'];

    protected array $included = ['featured_image', 'lowest_price', 'lowest_compare_at_price', 'currency', 'default_variant'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the lowest priced variant for this product
     */
    public function getLowestPriceAttribute()
    {
        return $this->getDefaultVariantAndLowestPrice()['price'];
    }

    /**
     * Get the lowest compare_at_price for this product
     */
    public function getLowestCompareAtPriceAttribute()
    {
        if (!$this->relationLoaded('variants')) {
            $this->load('variants.userProductVariants.prices');
        }

        $lowestCompareAtPrice = null;
        foreach ($this->variants as $variant) {
            if (!$variant->relationLoaded('userProductVariants')) {
                $variant->load('userProductVariants.prices');
            }

            $userVariant = $variant->userProductVariants->first();
            if ($userVariant) {
                if (!$userVariant->relationLoaded('prices')) {
                    $userVariant->load('prices');
                }

                $price = $userVariant->prices->first();
                if ($price && $price->compare_at_price !== null) {
                    if ($lowestCompareAtPrice === null || $price->compare_at_price < $lowestCompareAtPrice) {
                        $lowestCompareAtPrice = $price->compare_at_price;
                    }
                }
            }
        }

        return $lowestCompareAtPrice;
    }

    /**
     * Get the currency (from first variant)
     */
    public function getCurrencyAttribute()
    {
        if (!$this->relationLoaded('variants')) {
            $this->load('variants.userProductVariants.prices');
        }

        $firstVariant = $this->variants->first();
        if ($firstVariant) {
            return $firstVariant->currency ?? config('commerce.default_currency', 'KES');
        }

        return config('commerce.default_currency', 'KES');
    }

    /**
     * Get the default variant (lowest priced, enabled variant)
     */
    public function getDefaultVariantAttribute()
    {
        return $this->getDefaultVariantAndLowestPrice()['variant'];
    }

    /**
     * Get the default variant and lowest price (lowest priced, enabled variant)
     */
    public function getDefaultVariantAndLowestPrice()
    {
        if (!$this->relationLoaded('variants')) {
            $this->load('variants.userProductVariants.prices');
        }

        $lowestPrice = null;
        $defaultVariant = null;

        foreach ($this->variants as $variant) {
            if (!$variant->enabled) {
                continue;
            }

            if (!$variant->relationLoaded('userProductVariants')) {
                $variant->load('userProductVariants.prices');
            }

            $userVariant = $variant->userProductVariants->first();
            if ($userVariant) {
                if (!$userVariant->relationLoaded('prices')) {
                    $userVariant->load('prices');
                }

                $price = $userVariant->prices->first();
                if ($price && $price->price !== null) {
                    if ($lowestPrice === null || $price->price < $lowestPrice) {
                        $lowestPrice = $price->price;
                        $defaultVariant = $variant;
                    }
                }
            }
        }

        return [
            'variant' => $defaultVariant,
            'price' => $lowestPrice,
        ];
    }
}
