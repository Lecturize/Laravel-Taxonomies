<?php namespace Lecturize\Taxonomies\Traits;

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
        return $this->morphToMany(Taxonomy::class, 'taxable');
    }

    /**
     * Convenience method to set categories.
     *
     * @param string  $terms
     * @param string  $taxonomy
     */
    public function setCategories($terms, $taxonomy)
    {
        $terms = explode('|', $terms);
        $terms = Term::whereIn('slug', $terms)->pluck('id')->all();

        if (count($terms) > 0)
            foreach ($terms as $term) {
                if ($taxonomy = Taxonomy::where('taxonomy', $taxonomy)->where('term_id', $term)->first())
                    $this->attachTaxonomy($taxonomy->id);
            }
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
     * Add one or multiple terms in a given taxonomy.
     *
     * @param mixed    $terms
     * @param string   $taxonomy
     * @param integer  $parent_id
     * @param integer  $order
     */
    public function addCategories($terms, $taxonomy, $parent_id = null, $order = null)
    {
        $terms = TaxableUtils::makeTermsArray($terms);

        $this->createTaxables($terms, $taxonomy, $parent_id, $order);

        $terms = Term::whereIn('title', $terms)->pluck('id')->all();

        if (count($terms) > 0) {
            foreach ($terms as $term) {
                if ($this->taxonomies()->where('taxonomy', $taxonomy)->where('term_id', $term)->first())
                    continue;

                $tax = Taxonomy::where('term_id', $term)->first();
                $this->taxonomies()->attach($tax->id);
            }
        } else {
            $this->taxonomies()->detach();
        }
    }

    /**
     * Add one or multiple terms in a given taxonomy.
     * @deprecated Use addCategories() instead.
     *
     * @param mixed    $terms
     * @param string   $taxonomy
     * @param integer  $parent_id
     * @param integer  $order
     */
    public function addTerm($terms, $taxonomy, $parent_id = null, $order = null)
    {
        $this->addCategories($terms, $taxonomy, $parent_id, $order);
    }

    /**
     * Create terms and taxonomies (taxables).
     *
     * @param mixed    $terms
     * @param string   $taxonomy
     * @param integer  $parent_id
     * @param integer  $order
     */
    public function createTaxables($terms, $taxonomy, $parent_id = null, $order = null)
    {
        $terms = TaxableUtils::makeTermsArray($terms);

        TaxableUtils::createTerms($terms);
        TaxableUtils::createTaxonomies($terms, $taxonomy, $parent_id, $order);
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
     * Get the terms related to a given taxonomy.
     *
    * @param  string  $taxonomy
     * @return mixed
     */
    public function getTerms($taxonomy = '')
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
    public function getTerm($term_title, $taxonomy = '')
    {
        if ($taxonomy) {
            $term_ids = $this->taxonomies()->where('taxonomy', $taxonomy)->pluck('term_id');
        } else {
            $term_ids = $this->taxonomies()->pluck('term_id');
        }

        return Term::whereIn('id', $term_ids)->where('title', $term_title)->first();
    }

    /**
     * Check if this model has a given term.
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
     * Check if this model has a given term.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return boolean
     */
    public function hasCategory($term_title, $taxonomy = '')
    {
        return (bool) $this->getTerm($term_title, $taxonomy);
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
     * Detach the given category from this model.
     *
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return mixed
     */
    public function detachCategory($term_title, $taxonomy = '')
    {
        if (! $term = $this->getTerm($term_title, $taxonomy))
            return null;

        if ($taxonomy) {
            $taxonomy = $this->taxonomies()->where('taxonomy', $taxonomy)->where('term_id', $term->id)->first();
        } else {
            $taxonomy = $this->taxonomies()->where('term_id', $term->id)->first();
        }

        return $this->taxonomies()->detach($taxonomy->id);
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
     * Detach all categories (related taxonomies via taxable table) from this model.
     *
     * @return mixed
     */
    public function detachCategories()
    {
        return $this->taxonomies()->detach();
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
     * Scope that have been categorized in given terms (category titles) and taxonomy.
     *
     * @param  object  $query
     * @param  array   $term_titles  The term titles as an array.
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeCategorizedIn($query, $term_titles, $taxonomy)
    {
        $terms = TaxableUtils::makeTermsArray($term_titles);

        foreach ($terms as $term)
            $this->scopeCategorized($query, $term, $taxonomy);

        return $query;
    }

    /**
     * Scope that have been categorized in given term (category title) and taxonomy.
     * Example: scope term "Shoes" in taxonomy "shop_cat" (shop category) or
     * scope term "Shoes" in taxonomy "blog_cat" (blog articles).
     *
     * @param  object  $query
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeCategorized($query, $term_title, $taxonomy)
    {
        $term_ids = Taxonomy::where('taxonomy', $taxonomy)->pluck('term_id');
        $term     = Term::whereIn('id', $term_ids)->where('title', $term_title)->first();

        return $query->whereHas('taxonomies', function($q) use($term) {
            $q->where('term_id', $term->id);
        });
    }

    /**
     * Scope by given taxonomy.
     * @deprecated Will be removed in the future.
     *
     * @param  object  $query
     * @param  string  $term_title
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeWithTax($query, $term_title, $taxonomy)
    {
        $term_ids = Taxonomy::where('taxonomy', $taxonomy)->pluck('term_id');
        $term     = Term::whereIn('id', $term_ids)->where('title', $term_title)->first();

        $taxonomy = Taxonomy::where('term_id', $term->id)->first();

        return $query->whereHas('taxed', function($q) use($term, $taxonomy) {
            $q->where('taxonomy_id', $taxonomy->id);
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
        return $this->scopeHasTaxonomy($query, $taxonomy_id);
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
        return $this->scopeHasTaxonomies($query, $taxonomy_ids);
    }

    /**
     * Scope by category id.
     *
     * @param  object   $query
     * @param  integer  $taxonomy_id
     * @return mixed
     */
    public function scopeHasTaxonomy($query, $taxonomy_id)
    {
        return $query->whereHas('taxed', function($q) use($taxonomy_id) {
            $q->where('taxonomy_id', $taxonomy_id);
        });
    }

    /**
     * Scope by category ids.
     *
     * @param  object  $query
     * @param  array   $taxonomy_ids
     * @return mixed
     */
    public function scopeHasTaxonomies($query, $taxonomy_ids)
    {
        return $query->whereHas('taxed', function($q) use($taxonomy_ids) {
            $q->whereIn('taxonomy_id', $taxonomy_ids);
        });
    }
}