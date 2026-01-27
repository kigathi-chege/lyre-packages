<?php

namespace Lyre\Commerce\Repositories;

use Lyre\Repository;
use Lyre\Commerce\Models\Product;
use Lyre\Commerce\Repositories\Contracts\ProductRepositoryInterface;

class ProductRepository extends Repository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function all($callbacks = [], $paginate = true)
    {
        // Add facet filtering support
        if (array_key_exists('facet', request()->query())) {
            $facetSlug = request()->query('facet');
            $facetValueSlug = request()->query('facet_value');

            $callbacks[] = function ($query) use ($facetSlug, $facetValueSlug) {
                $facet = \Lyre\Facet\Models\Facet::with('facetValues')->where('slug', $facetSlug)->first();

                if ($facet) {
                    $facetValueIds = $facet->facetValues->pluck('id');

                    // If facet_value is specified, filter by that specific value
                    if ($facetValueSlug) {
                        $facetValue = \Lyre\Facet\Models\FacetValue::where('slug', $facetValueSlug)
                            ->where('facet_id', $facet->id)
                            ->first();
                        if ($facetValue) {
                            $facetValueIds = [$facetValue->id];
                        }
                    }

                    return $query->whereHas('facetValues', function ($q) use ($facetValueIds) {
                        $prefix = config('lyre.table_prefix');
                        $q->whereIn("{$prefix}facet_values.id", $facetValueIds);
                    });
                }

                return $query;
            };
        }

        return parent::all($callbacks, $paginate);
    }
}
