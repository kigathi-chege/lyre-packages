<?php

namespace Lyre\Commerce\Http\Resources;

use Lyre\Commerce\Models\Product as ProductModel;
use Lyre\Resource;
use Illuminate\Http\Request;

class Product extends Resource
{
    public function __construct(ProductModel $model)
    {
        parent::__construct($model);
    }

    // public static function loadResources($resource = null): array
    // {
    //     // When Product is loaded as a relationship from ProductVariant,
    //     // we should not load variants to prevent circular references.
    //     // The ProductVariantResource will handle removing variants from the serialized product.
    //     // Always return the relationships, but ProductVariantResource will clean them up.
    //     return [
    //         'variants' => \Lyre\Commerce\Http\Resources\ProductVariant::class,
    //         'facetValues' => \Lyre\Facet\Http\Resources\FacetValue::class,
    //         'files' => \Lyre\File\Http\Resources\File::class,
    //     ];
    // }

    // public function toArray($request): array
    // {
    //     $data = parent::toArray($request);

    //     // Ensure featured_image is properly serialized
    //     if ($this->resource->relationLoaded('files')) {
    //         $featuredImage = $this->resource->featured_image;
    //         if ($featuredImage) {
    //             if ($featuredImage instanceof \Lyre\Resource) {
    //                 $data['featured_image'] = $featuredImage->toArray($request);
    //             } else {
    //                 $data['featured_image'] = $featuredImage;
    //             }
    //         }
    //     }

    //     // Serialize default_variant if available
    //     if (isset($this->resource->default_variant) && $this->resource->default_variant) {
    //         $defaultVariant = $this->resource->default_variant;
    //         if ($defaultVariant instanceof \Lyre\Resource) {
    //             $data['default_variant'] = $defaultVariant->toArray($request);
    //         } else {
    //             $data['default_variant'] = $defaultVariant;
    //         }
    //     }

    //     // Ensure variants are properly serialized but prevent circular references
    //     if (isset($data['variants']) && is_array($data['variants'])) {
    //         foreach ($data['variants'] as $key => $variant) {
    //             if ($variant instanceof \Lyre\Resource) {
    //                 $variantData = $variant->toArray($request);
    //                 // Remove product from variant to prevent circular reference
    //                 unset($variantData['product']);
    //                 $data['variants'][$key] = $variantData;
    //             }
    //         }
    //     }

    //     return $data;
    // }
}
