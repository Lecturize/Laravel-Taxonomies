<?php namespace Lecturize\Taxonomies\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Lecturize\Taxonomies\Models\Taxable;
use Lecturize\Taxonomies\Models\Taxonomy;
use Lecturize\Taxonomies\Models\Term;

/**
 * Class HasCategories
 * @package Lecturize\Taxonomies\Traits
 * @property EloquentCollection|Taxonomy[]  $taxonomies
 * @property EloquentCollection|Taxable[]   $taxable
 *
 * @method static Builder withinTaxonomy(Taxonomy|int|null $taxonomy)
 * @method static Builder withinTaxonomies(Collection|array|null $taxonomies)
 */
trait HasCategories
{
    /**
     * Return a collection of taxonomies for this model.
     */
    public function taxonomies(): MorphToMany
    {
        /** @var Model $this */
        return $this->morphToMany(
            config('lecturize.taxonomies.taxonomies.model', Taxonomy::class),
            'taxable'
        );
    }

    /**
     * Return a collection of taxonomies related to the taxed model.
     */
    public function taxable(): MorphMany
    {
        /** @var Model $this */
        return $this->morphMany(
            config('lecturize.taxonomies.pivot.model', Taxable::class),
            'taxable'
        );
    }

    /**
     * Convenience method to sync categories.
     */
    public function syncCategories(string $terms, string $taxonomy): void
    {
        $this->detachCategories();
        $this->setCategories($terms, $taxonomy);
    }

    /**
     * Convenience method for attaching a given taxonomy to this model.
     */
    public function attachTaxonomy(int $taxonomy_id): void
    {
        if (! $this->taxonomies()->where('id', $taxonomy_id)->first())
            $this->taxonomies()->attach($taxonomy_id);
    }

    /**
     * Convenience method for detaching a given taxonomy to this model.
     */
    public function detachTaxonomy(int $taxonomy_id): void
    {
        if ($this->taxonomies()->where('id', $taxonomy_id)->first())
            $this->taxonomies()->detach($taxonomy_id);
    }

    /**
     * Convenience method to set categories.
     */
    public function setCategories(string|array $categories, string $taxonomy): void
    {
        $this->detachCategories();
        $this->addCategories($categories, $taxonomy);
    }

    /**
     * Add one or multiple terms (categories) within a given taxonomy.
     */
    public function addCategories(string|array $categories, string $taxonomy, ?Taxonomy $parent = null): self
    {
        $taxonomies = \Lecturize\Taxonomies\Facades\Taxonomy::createCategories($categories, $taxonomy, $parent);

        if (count($taxonomies) > 0)
            foreach ($taxonomies as $taxonomy)
                $this->taxonomies()->attach($taxonomy->id);

        return $this;
    }

    /**
     * Convenience method to add category to this model.
     */
    public function addCategory(string|array $categories, string $taxonomy, ?Taxonomy $parent = null): self
    {
        return $this->addCategories($categories, $taxonomy, $parent);
    }

    /**
     * Add one or multiple terms in a given taxonomy.
     * @deprecated Use addCategory() or addCategories() instead.
     */
    public function addTerm(string|array $categories, string $taxonomy, ?Taxonomy $parent = null): self
    {
        return $this->addCategory($categories, $taxonomy, $parent);
    }

    /**
     * Pluck terms for a given taxonomy by name.
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
     */
    public function getTermNames(string $taxonomy = ''): Collection
    {
        return $this->getTermTitles($taxonomy);
    }

    /**
     * Get the terms (categories) for this item within the given taxonomy.
     */
    public function getCategories(string $taxonomy = ''): Collection
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
     */
    public function getCategory(string $term_title, string $taxonomy = ''): ?Term
    {
        $terms = $this->getCategories($taxonomy);

        return $terms->where('title', $term_title)->first();
    }

    /**
     * Get the terms (categories) for this item within the given taxonomy.
     * @deprecated Use getCategories() instead.
     */
    public function getTerms(string $taxonomy = ''): Collection
    {
        return $this->getCategories($taxonomy);
    }

    /**
     * Get a term model by the given name and optionally a taxonomy.
     * @deprecated Use getCategory() instead.
     */
    public function getTerm(string $term_title, string $taxonomy = ''): ?Term
    {
        return $this->getCategory($term_title, $taxonomy);
    }

    /**
     * Check if this model belongs to a given category.
     */
    public function hasCategory(string $term_title, string $taxonomy = ''): bool
    {
        return (bool) $this->getCategory($term_title, $taxonomy);
    }

    /**
     * Check if this model belongs to a given category.
     * @deprecated Seemed confusing, use hasCategory() instead.
     */
    public function hasTerm(string $term_title, string $taxonomy = ''): bool
    {
        return $this->hasCategory($term_title, $taxonomy);
    }

    /**
     * Detach the given category from this model.
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
     */
    public function removeTerm(string $term_title, string $taxonomy = ''): ?int
    {
        return $this->detachCategory($term_title, $taxonomy);
    }

    /**
     * Detach all categories (related taxonomies via taxable table) from this model.
     */
    public function detachCategories(): int
    {
        return $this->taxonomies()->detach();
    }

    /**
     * Detach all terms from this model.
     * @deprecated Use detachCategories() instead.
     */
    public function removeAllTerms(): int
    {
        return $this->detachCategories();
    }

    /**
     * Scope by the given term.
     * @deprecated This seemed confusing, use scopeCategorized() instead.
     */
    public function scopeWithTerm(Builder $query, string $category, string $taxonomy): Builder
    {
        return $this->scopeCategorized($query, $category, $taxonomy);
    }

    /**
     * Scope that have been categorized in given term (category title) and taxonomy.
     * Example: scope term "Shoes" in taxonomy "shop_cat" (shop category) or
     * scope term "Shoes" in taxonomy "blog_cat" (blog articles).
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
     */
    public function scopeWithTerms(Builder $query, array $categories, string $taxonomy): Builder
    {
        return $this->scopeCategorizedIn($query, $categories, $taxonomy);
    }

    /**
     * Scope that have been categorized in given terms (category titles) and taxonomy.
     * @param  array|string  $categories
     */
    public function scopeCategorizedIn(Builder $query, string|array $categories, string $taxonomy): Builder
    {
        if (is_string($categories))
            $categories = explode('|', $categories);

        foreach ($categories as $term)
            $this->scopeCategorized($query, $term, $taxonomy);

        return $query;
    }

    /**
     * Scope by given taxonomy.
     *
     * @param  Builder            $query
     * @param  Taxonomy|int|null  $taxonomy
     * @return Builder
     */
    public function scopeWithinTaxonomy(Builder $query, Taxonomy|int|null $taxonomy): Builder
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
     * Scope by given taxonomy.
     * @deprecated This seemed confusing, use scopeWithinTaxonomy() instead.
     *
     * @param  Builder            $query
     * @param  Taxonomy|int|null  $taxonomy
     * @return Builder
     */
    public function scopeHasCategory(Builder $query, Taxonomy|int|null $taxonomy): Builder
    {
        return $this->scopeWithinTaxonomy($query, $taxonomy);
    }

    /**
     * Scope by taxonomies.
     *
     * @param  Builder                           $query
     * @param  Collection|Taxonomy[]|array|null  $taxonomies
     * @return Builder
     */
    public function scopeWithinTaxonomies(Builder $query, Collection|array|null $taxonomies): Builder
    {
        if ($taxonomies instanceof Collection) {
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
     * Scope by taxonomies.
     * @deprecated This seemed confusing, use scopeHasTaxonomies() instead.
     *
     * @param  Builder                           $query
     * @param  Collection|Taxonomy[]|array|null  $taxonomies
     * @return Builder
     */
    public function scopeHasCategories(Builder $query, Collection|array|null $taxonomies): Builder
    {
        return $this->scopeWithinTaxonomies($query, $taxonomies);
    }
}
