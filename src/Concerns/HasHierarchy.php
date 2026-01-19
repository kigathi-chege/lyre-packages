<?php

namespace Lyre\Facet\Concerns;

// NOTE: Kigathi - January 19 2026 - Only add this to repositories that need it

trait HasHierarchy
{
    public function hierarchy(?string $modelId = null, ?array $scope = null)
    {
        // Base query: either a specific root or all roots
        $query = $this->model::query()
            ->with(['children' => function ($q) {
                $q->orderBy('order');
            }])
            ->orderBy('order');

        if ($modelId) {
            // Ensure we only fetch the requested model
            $idColumn = $this->model::ID_COLUMN;
            $query->where($idColumn, $modelId);
        } else {
            // Default: fetch top-level facets
            $query->roots($scope);
        }

        $facets = $query->get();

        // Recursive tree builder
        $buildTree = function ($facets, int $depth = 0) use (&$buildTree) {
            return $facets->map(function ($facet) use (&$buildTree, $depth) {
                $resource = new $this->resource($facet);
                $data = [
                    ...$resource->resource->toArray(),
                    'depth' => $depth,
                ];

                if ($facet->children->isNotEmpty()) {
                    $data['children'] = $buildTree($facet->children, $depth + 1);
                }

                return $data;
            });
        };

        $hierarchy = $buildTree($facets);

        return $hierarchy;
    }
}
