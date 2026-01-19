<?php

namespace Lyre\Facet\Http\Resources;

use Lyre\Facet\Models\FacetValue as FacetValueModel;
use Lyre\Resource;
use Illuminate\Http\Request;

class FacetValue extends Resource
{
    public function __construct(FacetValueModel $model)
    {
        parent::__construct($model);
    }

    // public function toArray($request): array
    // {
    //     $data = parent::toArray($request);

    //     // If facet is loaded, prevent it from loading its parent/children
    //     // to avoid circular references
    //     if (isset($data['facet']) && is_array($data['facet'])) {
    //         unset($data['facet']['parent']);
    //         unset($data['facet']['children']);
    //     }

    //     return $data;
    // }
}
