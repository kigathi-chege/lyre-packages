<?php

namespace Lyre\Facet\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Lyre\Facet\Models\FacetValue;
use Lyre\Facet\Repositories\Contracts\FacetValueRepositoryInterface;
use Lyre\Controller;

class FacetValueController extends Controller
{
    public function __construct(
        FacetValueRepositoryInterface $modelRepository
    ) {
        $model = new FacetValue();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }

    /**
     * Get the facet value hierarchy
     * If $facetValueId is provided, returns the hierarchy rooted at that facet.
     * Otherwise, returns the full hierarchy.
     */
    public function hierarchy(?string $facetValueId = null): JsonResponse
    {
        $scope = null;
        $facet = request('facet');

        if ($facet) {
            $scope = ['facet' => $facet];
        }

        return __response(
            true,
            "Get Hierarchy {$this->modelNamePlural}",
            $this->modelRepository->hierarchy($facetValueId, $scope),
            get_response_code("get-{$this->modelName}")
        );
    }
}
