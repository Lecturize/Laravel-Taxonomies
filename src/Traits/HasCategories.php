<?php namespace Lecturize\Taxonomies\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Lecturize\Taxonomies\Models\Taxable;
use Lecturize\Taxonomies\Models\Taxonomy;
use Lecturize\Taxonomies\Models\Term;

/**
 * Class HasCategories
 * @package Lecturize\Taxonomies\Traits
 * @property EloquentCollection  $taxonomies
 * @property EloquentCollection  $taxable
 */
trait HasCategories
{
    /**
     * Return a collection of taxonomies for this model.
     *
     * @return MorphToMany
     */
    public function taxonomies(): MorphToMany
    {
        return $this->morphToMany(
            config('lecturize.taxonomies.taxonomies.model', Taxonomy::class),
            'taxable'
        );
    }

    /**
     * Return a collection of taxonomies related to the taxed model.
     *
     * @return MorphMany
     */
    public function taxable(): MorphMany
    {
        return $this->morphMany(
            config('lecturize.taxonomies.pivot.model', Taxable::class),
            'taxable'
        );
    }

    /**
     * Convenience method to sync categories.
     *
     * @param string  $terms
     * @param string  $taxonomy
     */
    public function syncCategories(string $terms, string $taxonomy)
    {
        $this->detachCategories();
        $this->setCategories($terms, $taxonomy);
    }

    /**
     * Convenience method for attaching a given taxonomy to this model.
     *
     * @param int  $taxonomy_id
     */
    public function attachTaxonomy(int $taxonomy_id)
    {
        if (! $this->taxonomies()->where('id', $taxonomy_id)->first())
            $this->taxonomies()->attach($taxonomy_id);
    }

    /**
     * Convenience method for detaching a given taxonomy to this model.
     *
     * @param int  $taxonomy_id
     */
    public function detachTaxonomy(int $taxonomy_id)
    {
        if ($this->taxonomies()->where('id', $taxonomy_id)->first())
            $this->taxonomies()->detach($taxonomy_id);
    }

    /**
     * Convenience method to set categories.
     *
     * @param string  $categories
     * @param string  $taxonomy
     */
    public function setCategories(string $categories, string $taxonomy)
    {
        $this->detachCategories();
        $this->addCategories($categories, $taxonomy);
    }

    /**
     * Add one or multiple terms (categories) within a given taxonomy.
     *
     * @param  string|array   $categories
     * @param  string         $taxonomy
     * @param  Taxonomy|null  $parent
     * @return self
     */
    public function addCategories($categories, string $taxonomy, ?Taxonomy $parent = null): self
    {
        $taxonomies = \Lecturize\Taxonomies\Facades\Taxonomy::createCategories($categories, $taxonomy, $parent);

        if (count($taxonomies) > 0)
            foreach ($taxonomies as $taxonomy)
                $this->taxonomies()->attach($taxonomy->id);

        return $this;
    }

    /**
     * Convenience method to add category to this model.
     *
     * @param  string|array   $categories
     * @param  string         $taxonomy
     * @param  Taxonomy|null  $parent
     * @return self
     */
    public function addCategory($categories, string $taxonomy, ?Taxonomy $parent = null): self
    {
        return $this->addCategories($categories, $taxonomy, $parent);
    }

    /**
     * Add one or multiple terms in a given taxonomy.
     * @deprecated Use addCategory() or addCategories() instead.
     *
     * @param  string|array   $categories
     * @param  string         $taxonomy
     * @param  Taxonomy|null  $parent
     * @return $this
     */
    public function addTerm($categories, string $taxonomy, ?Taxonomy $parent = null): self
    {
        return $this->addCategory($categories, $taxonomy, $parent);
    }

    /**
     * Pluck terms for a given taxonomy by name.
     *
     * @param  string  $taxonomy
     * @return Collection
     */
    public function getTermTitles(string $taxonomy = ''): Collection
    {
        if ($terms = $this->getCategories($taxonomy))
            return $terms->pluck('title');

        return collect();
    }

    /**
     * Pluck terms for a given taxonomy by name.
     * @deprecated Use getTermTitles() instead.
     *
     * @param  string  $taxonomy
     * @return Collection
     */
    public function getTermNames(string $taxonomy = ''): Collection
    {
        return $this->getTermTitles($taxonomy);
    }

    /**
     * Get the terms (categories) for this item within the given taxonomy.
     *
     * @param  string  $taxonomy
     * @return Collection
     */
    public function getCategories($taxonomy = ''): Collection
    {
        if ($taxonomy) {
            $term_ids = $this->taxonomies()->where('taxonomy', $taxonomy)->pluck('term_id');
        } else {
            $term_ids = $this->taxonomies()->pluck('term_id');
        }

        return Term::whereIn('id', $term_ids)->get();
    }

    /**
     * Get a term model by the given name and optionally a taxonomy.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return Term|null
     */
    public function getCategory(string $term_title, string $taxonomy = ''): ?Term
    {
        $terms = $this->getCategories($taxonomy);

        return $terms->where('title', $term_title)->first();
    }

    /**
     * Get the terms (categories) for this item within the given taxonomy.
     * @deprecated Use getCategories() instead.
     *
     * @param  string  $taxonomy
     * @return Collection
     */
    public function getTerms($taxonomy = ''): Collection
    {
        return $this->getCategories($taxonomy);
    }

    /**
     * Get a term model by the given name and optionally a taxonomy.
     * @deprecated Use getCategory() instead.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return Term|null
     */
    public function getTerm(string $term_title, string $taxonomy = ''): ?Term
    {
        return $this->getCategory($term_title, $taxonomy);
    }

    /**
     * Check if this model belongs to a given category.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return bool
     */
    public function hasCategory(string $term_title, string $taxonomy = ''): bool
    {
        return (bool) $this->getCategory($term_title, $taxonomy);
    }

    /**
     * Check if this model belongs to a given category.
     * @deprecated Seemed confusing, use hasCategory() instead.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return bool
     */
    public function hasTerm(string $term_title, string $taxonomy = ''): bool
    {
        return $this->hasCategory($term_title, $taxonomy);
    }

    /**
     * Detach the given category from this model.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return int|null
     */
    public function detachCategory(string $term_title, string $taxonomy = ''): ?int
    {
        if (! $term = $this->getCategory($term_title, $taxonomy))
            return null;

        if ($taxonomy) {
            $taxonomy = $this->taxonomies()->where('taxonomy', $taxonomy)->where('term_id', $term->id)->first();
        } else {
            $taxonomy = $this->taxonomies()->where('term_id', $term->id)->first();
        }

        return $this->taxonomies()->detach($taxonomy->id);
    }

    /**
     * Detach the given category from this model.
     * @deprecated Seemed confusing, use detachCategory() instead.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return int|null
     */
    public function removeTerm(string $term_title, string $taxonomy = ''): ?int
    {
        return $this->detachCategory($term_title, $taxonomy);
    }

    /**
     * Detach all categories (related taxonomies via taxable table) from this model.
     *
     * @return int
     */
    public function detachCategories(): int
    {
        return $this->taxonomies()->detach();
    }

    /**
     * Detach all terms from this model.
     * @deprecated Use detachCategories() instead.
     *
     * @return int
     */
    public function removeAllTerms(): int
    {
        return $this->detachCategories();
    }

    /**
     * Scope by the given term.
     * @deprecated This seemed confusing, use scopeCategorized() instead.
     *
     * @param  Builder  $query
     * @param  string   $category
     * @param  string   $taxonomy
     * @return Builder
     */
    public function scopeWithTerm(Builder $query, string $category, string $taxonomy): Builder
    {
        return $this->scopeCategorized($query, $category, $taxonomy);
    }

    /**
     * Scope that have been categorized in given term (category title) and taxonomy.
     * Example: scope term "Shoes" in taxonomy "shop_cat" (shop category) or
     * scope term "Shoes" in taxonomy "blog_cat" (blog articles).
     *
     * @param  Builder  $query
     * @param  string   $category
     * @param  string   $taxonomy
     * @return Builder
     */
    public function scopeCategorized(Builder $query, string $category, string $taxonomy): Builder
    {
        $term_ids = Taxonomy::where('taxonomy', $taxonomy)->pluck('term_id');
        $term     = Term::whereIn('id', $term_ids)->where('title', $category)->first();

        return $query->whereHas('taxonomies', function($q) use($term) {
            $q->where('term_id', $term->id);
        });
    }

    /**
     * Scope by given terms.
     * @deprecated This seemed confusing, use scopeCategorizedIn() instead.
     *
     * @param  Builder  $query
     * @param  array    $categories
     * @param  string   $taxonomy
     * @return Builder
     */
    public function scopeWithTerms(Builder $query, array $categories, string $taxonomy): Builder
    {
        return $this->scopeCategorizedIn($query, $categories, $taxonomy);
    }

    /**
     * Scope that have been categorized in given terms (category titles) and taxonomy.
     *
     * @param  Builder  $query
     * @param  array    $categories
     * @param  string   $taxonomy
     * @return Builder
     */
    public function scopeCategorizedIn(Builder $query, array $categories, string $taxonomy): Builder
    {
        if (is_string($categories))
            $categories = explode('|', $categories);

        foreach ($categories as $term)
            $this->scopeCategorized($query, $term, $taxonomy);

        return $query;
    }

    /**
     * Scope by the taxonomy id.
     *
     * @param  Builder       $query
     * @param  Taxonomy|int  $taxonomy
     * @return Builder
     */
    public function scopeWithinTaxonomy(Builder $query, $taxonomy): Builder
    {
        if ($taxonomy instanceof Taxonomy) {
            $taxonomy_id = $taxonomy->id;
        } elseif (is_int($taxonomy)) {
            $taxonomy_id = $taxonomy;
        } else {
            return $query;
        }

        return $query->whereHas('taxonomies', function($q) use($taxonomy_id) {
            $q->where('taxonomy_id', $taxonomy_id);
        });
    }

    /**
     * Scope by category id.
     * @deprecated This seemed confusing, use scopeWithinTaxonomy() instead.
     *
     * @param  Builder       $query
     * @param  Taxonomy|int  $taxonomy
     * @return Builder
     */
    public function scopeHasCategory(Builder $query, $taxonomy): Builder
    {
        return $this->scopeWithinTaxonomy($query, $taxonomy);
    }

    /**
     * Scope by category ids.
     *
     * @param  Builder           $query
     * @param  Collection|array  $taxonomies
     * @return Builder
     */
    public function scopeWithinTaxonomies(Builder $query, $taxonomies): Builder
    {
        if (method_exists($taxonomies, 'pluck')) {
            $taxonomy_ids = $taxonomies->pluck('id')->toArray();
        } elseif (is_array($taxonomies)) {
            $taxonomy_ids = $taxonomies;
        } else {
            return $query;
        }

        return $query->whereHas('taxonomies', function($q) use($taxonomy_ids) {
            $q->whereIn('taxonomy_id', $taxonomy_ids);
        });
    }

    /**
     * Scope by category ids.
     * @deprecated This seemed confusing, use scopeHasTaxonomies() instead.
     *
     * @param  Builder           $query
     * @param  Collection|array  $taxonomies
     * @return Builder
     */
    public function scopeHasCategories(Builder $query, $taxonomies): Builder
    {
        return $this->scopeWithinTaxonomies($query, $taxonomies);
    }
}
