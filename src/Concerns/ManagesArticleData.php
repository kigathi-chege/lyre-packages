<?php

namespace Lyre\Content\Concerns;

use Illuminate\Support\Facades\Log;
use Lyre\Content\Models\Article;
use Lyre\Facet\Models\Facet;
use Lyre\Facet\Models\FacetValue;

trait ManagesArticleData
{
    /**
     * Attach categories to article
     */
    protected function attachCategories(Article $article, array $categoryNames): void
    {
        $categoryFacet = Facet::firstOrCreate(['name' => 'Blog Category']);
        $facetValueIds = [];

        foreach ($categoryNames as $categoryName) {
            Log::debug('🏷️ Creating/finding category', [
                'category' => $categoryName,
                'facet_id' => $categoryFacet->id,
            ]);

            $facetValue = FacetValue::firstOrCreate([
                'facet_id' => $categoryFacet->id,
                'name' => $categoryName,
            ]);
            $facetValueIds[] = $facetValue->id;
        }

        if (!empty($facetValueIds) && method_exists($article, 'attachFacetValues')) {
            $article->attachFacetValues($facetValueIds);
        }
    }

    /**
     * Update article categories (sync instead of attach)
     */
    protected function updateCategories(Article $article, array $categoryNames): void
    {
        Log::info('🏷️ Updating article categories', [
            'article_id' => $article->id,
            'categories' => $categoryNames,
        ]);

        $categoryFacet = Facet::where('name', 'Blog Category')->first();

        if (!$categoryFacet) {
            $categoryFacet = Facet::firstOrCreate(['name' => 'Blog Category']);
        }

        $facetValueIds = [];
        foreach ($categoryNames as $categoryName) {
            $facetValue = FacetValue::firstOrCreate(
                ['facet_id' => $categoryFacet->id, 'name' => $categoryName],
                ['status' => 'published']
            );
            $facetValueIds[] = $facetValue->id;
        }

        $article->facetValues()->sync($facetValueIds);

        Log::debug('✅ Categories updated', [
            'article_id' => $article->id,
            'category_count' => count($facetValueIds),
        ]);
    }

    /**
     * Clean filename to create a title
     */
    protected function cleanTitle(string $filename): string
    {
        // Remove common prefixes, numbers, etc.
        $title = preg_replace('/^\d+[-_.]/', '', $filename);
        $title = str_replace(['_', '-'], ' ', $title);
        $title = ucwords($title);
        return $title;
    }
}
