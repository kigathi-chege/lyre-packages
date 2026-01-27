<?php
namespace Lyre\Facet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lyre\Model;
use Lyre\Traits\HasModelRelationships;

class FacetValue extends Model
{
    use HasFactory, HasModelRelationships;

    const ID_COLUMN = 'slug';

    // protected $with = ['facet'];

    protected array $included = ['facet_name'];

    protected $casts = [
        'order' => 'integer',
    ];

    public function facet(): BelongsTo
    {
        return $this->belongsTo(Facet::class);
    }

    /**
     * Parent facet value (for hierarchical facets)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(FacetValue::class, 'parent_id');
    }

    /**
     * Child facet values (for hierarchical facets)
     */
    public function children(): HasMany
    {
        return $this->hasMany(FacetValue::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get all ancestors (parent, grandparent, etc.)
     */
    public function ancestors()
    {
        $ancestors = collect();
        $current   = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors->reverse();
    }

    /**
     * Get all descendants (children, grandchildren, etc.)
     */
    public function descendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    public function getFacetNameAttribute()
    {
        return $this->facet?->name;
    }

    /**
     * Scope: Filter by hierarchy level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->whereHas('facet', function ($q) use ($level) {
            $q->where('hierarchy_level', $level);
        });
    }

    /**
     * Scope: Get root values (no parent)
     */
    public function scopeRoots($query, ?array $scope = null)
    {
        $query = $query->whereNull('parent_id');

        if (! empty($scope)) {
            $relationFilters = $this->buildRelationFilters($scope);

            if (! empty($relationFilters)) {
                foreach ($relationFilters as $relation => $filter) {
                    $query = filter_by_relationship($query, $relation, $filter['column'], $filter['value']);
                }
            }
        }

        return $query;
    }
}
