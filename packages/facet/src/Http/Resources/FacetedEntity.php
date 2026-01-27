<?php

namespace Lyre\Facet\Http\Resources;

use Lyre\Facet\Models\FacetedEntity as FacetedEntityModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lyre\Resource;

class FacetedEntity extends Resource
{
    public function __construct(FacetedEntityModel $model)
    {
        parent::__construct($model);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function facetValue(): BelongsTo
    {
        return $this->belongsTo(FacetValue::class);
    }
}
