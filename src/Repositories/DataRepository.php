<?php

namespace Lyre\Content\Repositories;

use Lyre\Content\Models\Article;
use Lyre\Repository;
use Lyre\Content\Models\Data;
use Lyre\Content\Repositories\Contracts\DataRepositoryInterface;

class DataRepository extends Repository implements DataRepositoryInterface
{
    protected $model;

    public function __construct(Data $model)
    {
        parent::__construct($model);
    }

    public function resolve(Data $dataModel)
    {
        $key = 'section_data:' . $dataModel->id . ':' . md5(json_encode($dataModel->filters));

        return cache()->remember(
            $key,
            now()->addMinutes(10),
            fn() => $this->build($dataModel)
        );
    }

    // TODO: Kigathi - January 24 2026 - Ensure that we are reusing the already defined methods that exist in Repository
    public function build(Data $dataModel)
    {
        $model = $dataModel->type;

        if ($model == "\\" . Article::class) {
            $model::setExcludedSerializableColumns(['content']);
        }

        $repository = $model::resolveRepository();
        foreach ($dataModel->filters as $key => $value) {
            match ($key) {
                'orderByColumn' => $orderByColumn = $value,
                'orderByOrder' => $orderByOrder = $value,
                'limit' => $repository->limit((int)$value),
                'offset' => $repository->offset((int)$value),
                'unpaginated' => $value ? $repository->unPaginate() : null,
                default => null,
            };
        }

        if (array_key_exists('relation', $dataModel->filters)) {
            $parts = explode(",", $dataModel->filters['relation']);
            $result = [];
            $modelInstance = new $model;
            for ($i = 0; $i < count($parts); $i += 2) {
                if ($parts[$i]) {
                    $relatedModel = $modelInstance->{$parts[$i]}();
                    $relatedModelClass = get_class($relatedModel->getRelated());
                    $idColumn = $relatedModelClass::ID_COLUMN;
                    $idTable = (new $relatedModelClass)->getTable();
                    $result[$parts[$i]] = [
                        'column' => "$idTable.$idColumn",
                        'value' => $parts[$i + 1],
                    ];
                }
            }

            $repository->relationFilters($result);
        }

        $callbacks = null;
        if (array_key_exists('facet', $dataModel->filters)) {
            $callbacks = [
                function ($query) use ($dataModel) {
                    $facet = \Lyre\Facet\Models\Facet::with('facetValues')
                        ->where('slug', $dataModel->filters['facet'])
                        ->orWhere('name', $dataModel->filters['facet'])
                        ->first();

                    if (!$facet) {
                        return $query;
                    }

                    $facetValueIds = $facet->facetValues->pluck('id');

                    return $query->whereHas('facetValues', function ($q) use ($facetValueIds) {
                        $prefix = config('lyre.table_prefix');
                        $q->whereIn("{$prefix}facet_values.id", $facetValueIds);
                    });
                }
            ];
        }

        if (isset($orderByColumn)) {
            $repository->orderBy($orderByColumn, $orderByOrder ?? 'desc');
        }

        $results = $repository->all($callbacks);

        return $results;
    }
}
