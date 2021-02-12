<?php namespace Lecturize\Taxonomies\Traits;

use Lecturize\Taxonomies\Models\Taxable;
use Lecturize\Taxonomies\Models\Taxonomy;
use Lecturize\Taxonomies\Models\Term;
use Lecturize\Taxonomies\TaxableUtils;

/**
 * Class HasCategories
 * @package Lecturize\Taxonomies\Traits
 */
trait HasCategories
{
    /**
     * Return a collection of taxonomies for this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function taxonomies()
    {
        return $this->morphToMany(
            config('lecturize.taxonomies.taxonomies.model', Taxonomy::class),
            'taxable'
        );
    }

    /**
     * Return a collection of taxonomies related to the taxed model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function taxable()
    {
        return $this->morphMany(
            config('lecturize.taxonomies.pivot.model', Taxable::class),
            'taxable'
        );
    }

    /**
     * Convenience method to synch categories.
     *
     * @param string  $terms
     * @param string  $taxonomy
     */
    public function synchCategories($terms, $taxonomy)
    {
        $this->detachCategories();
        $this->setCategories($terms, $taxonomy);
    }

    /**
     * Convenience method for attaching a given taxonomy to this model.
     *
     * @param int  $taxonomy_id
     */
    public function attachTaxonomy($taxonomy_id)
    {
        if (! $this->taxonomies()->where('id', $taxonomy_id)->first())
            $this->taxonomies()->attach($taxonomy_id);
    }

    /**
     * Convenience method for detaching a given taxonomy to this model.
     *
     * @param int  $taxonomy_id
     */
    public function detachTaxonomy($taxonomy_id)
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
    public function setCategories($categories, $taxonomy)
    {
        $this->detachCategories();
        $this->addCategories($categories, $taxonomy);
    }

    /**
     * Add one or multiple terms (categories) within a given taxonomy.
     *
     * @param  string|array  $categories
     * @param  string        $taxonomy
     * @param  Taxonomy      $parent
     * @param  int           $sort
     * @return $this
     */
    public function addCategories($categories, $taxonomy, $parent = null, $sort = null)
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
     * @param  string|array  $terms
     * @param  string        $taxonomy
     * @param  Taxonomy      $parent
     * @param  int           $sort
     * @return $this
     */
    public function addCategory($terms, $taxonomy, $parent = null, $sort = null)
    {
        return $this->addCategories($terms, $taxonomy, $parent, $sort);
    }

    /**
     * Add one or multiple terms in a given taxonomy.
     * @deprecated Use addCategory() or addCategories() instead.
     *
     * @param  string|array  $terms
     * @param  string        $taxonomy
     * @param  Taxonomy      $parent
     * @param  int           $sort
     * @return $this
     */
    public function addTerm($terms, $taxonomy, $parent = null, $sort = null)
    {
        return $this->addCategory($terms, $taxonomy, $parent, $sort);
    }

    /**
     * Pluck terms for a given taxonomy by name.
     *
     * @param  string  $taxonomy
     * @return mixed
     */
    public function getTermTitles($taxonomy = '')
    {
        if ($terms = $this->getTerms($taxonomy))
            $terms->pluck('title');

        return null;
    }

    /**
     * Pluck terms for a given taxonomy by name.
     * @deprecated Use getTermTitles() instead.
     *
     * @param  string  $taxonomy
     * @return mixed
     */
    public function getTermNames($taxonomy = '')
    {
        return $this->getTermTitles($taxonomy);
    }

    /**
     * Get the terms (categories) for this item within the given taxonomy.
     *
     * @param  string  $taxonomy
     * @return mixed
     */
    public function getCategories($taxonomy = '')
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
     * @return mixed
     */
    public function getCategory($term_title, $taxonomy = '')
    {
        $terms = $this->getCategories();

        return $terms->where('title', $term_title)->first();
    }

    /**
     * Get the terms (categories) for this item within the given taxonomy.
     * @deprecated Use getCategories() instead.
     *
     * @param  string  $taxonomy
     * @return mixed
     */
    public function getTerms($taxonomy = '')
    {
        return $this->getCategories($taxonomy);
    }

    /**
     * Get a term model by the given name and optionally a taxonomy.
     * @deprecated Use getCategory() instead.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return mixed
     */
    public function getTerm($term_title, $taxonomy = '')
    {
        return $this->getCategory($term_title, $taxonomy);
    }

    /**
     * Check if this model belongs to a given category.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return boolean
     */
    public function hasCategory($term_title, $taxonomy = '')
    {
        return (bool) $this->getCategory($term_title, $taxonomy);
    }

    /**
     * Check if this model belongs to a given category.
     * @deprecated Seemed confusing, use hasCategory() instead.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return boolean
     */
    public function hasTerm($term_title, $taxonomy = '')
    {
        return $this->hasCategory($term_title, $taxonomy);
    }

    /**
     * Detach the given category from this model.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return mixed
     */
    public function detachCategory($term_title, $taxonomy = '')
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
     * @return mixed
     */
    public function removeTerm($term_title, $taxonomy = '')
    {
        return $this->detachCategory($term_title, $taxonomy);
    }

    /**
     * Detach all categories (related taxonomies via taxable table) from this model.
     *
     * @return mixed
     */
    public function detachCategories()
    {
        return $this->taxonomies()->detach();
    }

    /**
     * Detach all terms from this model.
     * @deprecated Use detachCategories() instead.
     *
     * @return mixed
     */
    public function removeAllTerms()
    {
        return $this->detachCategories();
    }

    /**
     * Scope by given terms.
     * @deprecated This seemed confusing, use scopeCategorizedIn() instead.
     *
     * @param  object  $query
     * @param  array   $term_titles
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeWithTerms($query, $term_titles, $taxonomy)
    {
        return $this->scopeCategorizedIn($query, $term_titles, $taxonomy);
    }

    /**
     * Scope by the given term.
     * @deprecated This seemed confusing, use scopeCategorized() instead.
     *
     * @param  object  $query
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeWithTerm($query, $term_title, $taxonomy)
    {
        return $this->scopeCategorized($query, $term_title, $taxonomy);
    }

    /**
     * Scope that have been categorized in given term (category title) and taxonomy.
     * Example: scope term "Shoes" in taxonomy "shop_cat" (shop category) or
     * scope term "Shoes" in taxonomy "blog_cat" (blog articles).
     *
     * @param  object  $query
     * @param  string  $category
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeCategorized($query, $category, $taxonomy)
    {
        $term_ids = Taxonomy::where('taxonomy', $taxonomy)->pluck('term_id');
        $term     = Term::whereIn('id', $term_ids)->where('title', $category)->first();

        return $query->whereHas('taxonomies', function($q) use($term) {
            $q->where('term_id', $term->id);
        });
    }

    /**
     * Scope that have been categorized in given terms (category titles) and taxonomy.
     *
     * @param  object  $query
     * @param  array   $categories  The category titles as an array.
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeCategorizedIn($query, $categories, $taxonomy)
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
     * @param  object   $query
     * @param  integer  $taxonomy_id
     * @return mixed
     */
    public function scopeWithinTaxonomy($query, $taxonomy_id)
    {
        return $query->whereHas('taxed', function($q) use($taxonomy_id) {
            $q->where('taxonomy_id', $taxonomy_id);
        });
    }

    /**
     * Scope by category id.
     * @deprecated This seemed confusing, use scopeHasTaxonomy() instead.
     *
     * @param  object   $query
     * @param  integer  $taxonomy_id
     * @return mixed
     */
    public function scopeHasCategory($query, $taxonomy_id)
    {
        return $this->scopeWithinTaxonomy($query, $taxonomy_id);
    }

    /**
     * Scope by category ids.
     *
     * @param  object  $query
     * @param  array   $taxonomy_ids
     * @return mixed
     */
    public function scopeWithinTaxonomies($query, $taxonomy_ids)
    {
        return $query->whereHas('taxed', function($q) use($taxonomy_ids) {
            $q->whereIn('taxonomy_id', $taxonomy_ids);
        });
    }

    /**
     * Scope by category ids.
     * @deprecated This seemed confusing, use scopeHasTaxonomies() instead.
     *
     * @param  object  $query
     * @param  array   $taxonomy_ids
     * @return mixed
     */
    public function scopeHasCategories($query, $taxonomy_ids)
    {
        return $this->scopeWithinTaxonomies($query, $taxonomy_ids);
    }
}