<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware(['api', \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class])
    ->group(function () {
        Route::get('facets/hierarchy/{facetId?}', [\Lyre\Facet\Http\Controllers\FacetController::class, 'hierarchy']);
        Route::get('facetvalues/hierarchy/{facetValueId?}', [\Lyre\Facet\Http\Controllers\FacetValueController::class, 'hierarchy']);

        Route::apiResources([
            'facets' => \Lyre\Facet\Http\Controllers\FacetController::class,
            'facetvalues' => \Lyre\Facet\Http\Controllers\FacetValueController::class,
        ]);
    });
