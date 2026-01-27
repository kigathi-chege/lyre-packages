<?php

namespace Lyre\Facet\Http\Controllers;

use Lyre\Facet\Models\Facet;
use Lyre\Facet\Repositories\Contracts\FacetRepositoryInterface;
use Lyre\Controller;
use Illuminate\Http\JsonResponse;
use Lyre\Facet\Concerns\HasHierarchy;
use Lyre\Facet\Http\Resources\Facet as ResourcesFacet;

class FacetController extends Controller
{
    public function __construct(
        FacetRepositoryInterface $modelRepository
    ) {
        $model = new Facet();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }

    /**
     * Get the facet hierarchy
     * If $facetId is provided, returns the hierarchy rooted at that facet.
     * Otherwise, returns the full hierarchy.
     */
    public function hierarchy(?string $facetId = null): JsonResponse
    {
        return __response(
            true,
            "Get Hierarchy {$this->modelNamePlural}",
            $this->modelRepository->hierarchy($facetId),
            get_response_code("get-{$this->modelName}")
        );
    }
}
