<?php

namespace Lyre\Facet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lyre\Model;

class Facet extends Model
{
    use HasFactory;

    const ID_COLUMN = 'slug';

    // Removed 'parent' and 'children' from default included to prevent circular references
    // They can still be loaded explicitly via query parameters (e.g., ?with=parent,children)
    protected array $included = ['parent_name'];

    public function facetValues()
    {
        return $this->hasMany(FacetValue::class);
    }

    /**
     * Parent facet (for hierarchical facets)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Facet::class, 'parent_id');
    }

    /**
     * Child facets (for hierarchical facets)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Facet::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get all ancestors (parent, grandparent, etc.)
     */
    public function ancestors()
    {
        $ancestors = collect();
        $current = $this->parent;

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

    /**
     * Scope: Get root facets (no parent)
     */
    public function scopeRoots($query)
    {
        $query = $query->whereNull('parent_id');

        return $query;
    }

    public function getParentNameAttribute()
    {
        return $this->parent?->name;
    }
}
