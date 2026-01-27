<?php

namespace Lyre\Facet\Http\Resources;

use Lyre\Facet\Models\Facet as FacetModel;
use Lyre\Resource;
use Illuminate\Http\Request;

class Facet extends Resource
{
    public function __construct(FacetModel $model)
    {
        parent::__construct($model);
    }

    // public static function loadResources($resource = null): array
    // {
    //     // Only load relationships if explicitly requested via query parameters
    //     // This prevents circular references when facets are loaded as relationships
    //     $relationships = parent::loadResources($resource);

    //     // Remove parent and children from automatic loading to prevent circular references
    //     // They can still be loaded explicitly via query parameters
    //     unset($relationships['parent']);
    //     unset($relationships['children']);

    //     return $relationships;
    // }

    // public function toArray($request): array
    // {
    //     $data = parent::toArray($request);

    //     // Always remove parent/children to prevent circular references
    //     // They can still be loaded explicitly via query parameters if needed
    //     // but won't be automatically included to avoid infinite recursion
    //     unset($data['parent']);
    //     unset($data['children']);

    //     return $data;
    // }
}
