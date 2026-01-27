<?php

namespace Lyre\Commerce\Http\Resources;

use Lyre\Commerce\Models\ProductVariant as ProductVariantModel;
use Lyre\Resource;
use Illuminate\Http\Request;

class ProductVariant extends Resource
{
    public function __construct(ProductVariantModel $model)
    {
        parent::__construct($model);
    }

    public static function loadResources($resource = null): array
    {
        // Don't load product relationship to prevent circular references
        // When variants are loaded as part of a product, we don't need product back
        return [];
    }

    public function toArray($request): array
    {
        $data = parent::toArray($request);
        
        // Manually serialize featured_image if available
        if (isset($this->resource->featured_image) && $this->resource->featured_image) {
            $featuredImage = $this->resource->featured_image;
            if ($featuredImage instanceof \Lyre\Resource) {
                $data['featured_image'] = $featuredImage->toArray($request);
            } else {
                $data['featured_image'] = $featuredImage;
            }
        }
        
        // Ensure product is NOT serialized to prevent circular reference
        unset($data['product']);
        
        return $data;
    }
}


