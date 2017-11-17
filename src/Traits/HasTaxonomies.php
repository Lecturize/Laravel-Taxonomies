<?php namespace Lecturize\Taxonomies\Traits;

use Lecturize\Taxonomies\Models\Taxable;
use Lecturize\Taxonomies\Models\Taxonomy;
use Lecturize\Taxonomies\Models\Term;
use Lecturize\Taxonomies\TaxableUtils;

/**
 * Class HasTaxonomies
 * @package Lecturize\Taxonomies\Traits
 */
trait HasTaxonomies
{
    /**
     * Return a collection of taxonomies related to the taxed model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function taxed()
    {
        return $this->morphMany(Taxable::class, 'taxable');
    }

    /**
     * Return a collection of taxonomies related to the taxed model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function taxonomies()
    {
        return $this->morphToMany(Taxonomy::class, 'taxable');
    }

    /**
     * Add one or multiple terms in a given taxonomy.
     *
     * @param mixed    $terms
     * @param string   $taxonomy
     * @param integer  $parent
     * @param integer  $order
     */
    public function addTerm($terms, $taxonomy, $parent = 0, $order = 0)
    {
        $terms = TaxableUtils::makeTermsArray($terms);

        $this->createTaxables($terms, $taxonomy, $parent, $order);

        $terms = Term::whereIn('name', $terms)->pluck('id')->all();

        if (count($terms) > 0) {
            foreach ($terms as $term) {
                if ($this->taxonomies()->where('taxonomy', $taxonomy)->where('term_id', $term)->first())
                    continue;

                $tax = Taxonomy::where('term_id', $term)->first();
                $this->taxonomies()->attach($tax->id);
            }

            return;
        }

        $this->taxonomies()->detach();
    }

    /**
     * Convenience method for attaching this models taxonomies to the given parent taxonomy.
     *
     * @param  integer  $taxonomy_id
     */
    public function setCategory($taxonomy_id)
    {
        $this->taxonomies()->attach($taxonomy_id);
    }

    /**
     * Create terms and taxonomies (taxables).
     *
     * @param mixed    $terms
     * @param string   $taxonomy
     * @param integer  $parent
     * @param integer  $order
     */
    public function createTaxables($terms, $taxonomy, $parent = 0, $order = 0)
    {
        $terms = TaxableUtils::makeTermsArray($terms);

        TaxableUtils::createTerms($terms);
        TaxableUtils::createTaxonomies($terms, $taxonomy, $parent, $order);
    }

    /**
     * Pluck taxonomies by given field.
     *
     * @param  string  $by
     * @return mixed
     */
    public function getTaxonomies($by = 'id')
    {
        return $this->taxonomies->pluck($by);
    }

    /**
     * Pluck terms for a given taxonomy by name.
     *
     * @param  string  $taxonomy
     * @return mixed
     */
    public function getTermNames($taxonomy = '')
    {
        if ($terms = $this->getTerms($taxonomy))
            $terms->pluck('name');

        return null;
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
            $term_ids = $this->taxonomies->where('taxonomy', $taxonomy)->pluck('term_id');
        } else {
            $term_ids = $this->getTaxonomies('term_id');
        }

        return Term::whereIn('id', $term_ids)->get();
    }

    /**
     * Get a term model by the given name and optionally a taxonomy.
     *
     * @param  string  $term_name
    * @param  string  $taxonomy
     * @return mixed
     */
    public function getTerm($term_name, $taxonomy = '')
    {
        if ($taxonomy) {
            $term_ids = $this->taxonomies->where('taxonomy', $taxonomy)->pluck('term_id');
        } else {
            $term_ids = $this->getTaxonomies('term_id');
        }

        return Term::whereIn('id', $term_ids)->where('name', $term_name)->first();
    }

    /**
     * Check if this model has a given term.
     *
     * @param  string  $term_name
     * @param  string  $taxonomy
     * @return boolean
     */
    public function hasTerm($term_name, $taxonomy = '')
    {
        return (bool) $this->getTerm($term_name, $taxonomy);
    }

    /**
     * Disassociate the given term from this model.
     *
     * @param  string  $term_name
     * @param  string  $taxonomy
     * @return mixed
     */
    public function removeTerm($term_name, $taxonomy = '')
    {
        if (! $term = $this->getTerm($term_name, $taxonomy))
            return null;

        if ($taxonomy) {
            $taxonomy = $this->taxonomies->where('taxonomy', $taxonomy)->where('term_id', $term->id)->first();
        } else {
            $taxonomy = $this->taxonomies->where('term_id', $term->id)->first();
        }

        return $this->taxed()->where('taxonomy_id', $taxonomy->id)->delete();
    }

    /**
     * Disassociate all terms from this model.
     *
     * @return mixed
     */
    public function removeAllTerms()
    {
        return $this->taxed()->delete();
    }

    /**
     * Scope by given terms.
     *
     * @param  object  $query
     * @param  array   $terms
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeWithTerms($query, $terms, $taxonomy)
    {
        $terms = TaxableUtils::makeTermsArray($terms);

        foreach ($terms as $term)
            $this->scopeWithTerm($query, $term, $taxonomy);

        return $query;
    }

    /**
     * Scope by the given term.
     *
     * @param  object  $query
     * @param  string  $term_name
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeWithTerm($query, $term_name, $taxonomy)
    {
        $term_ids = Taxonomy::where('taxonomy', $taxonomy)->pluck('term_id');

        $term     = Term::whereIn('id', $term_ids)->where('name', $term_name)->first();
        $taxonomy = Taxonomy::where('term_id', $term->id)->first();

        return $query->whereHas('taxonomies', function($q) use($term, $taxonomy) {
            $q->where('term_id', $term->id);
        });
    }

    /**
     * Scope by given taxonomy.
     *
     * @param  object  $query
     * @param  string  $term_name
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeWithTax($query, $term_name, $taxonomy)
    {
        $term_ids = Taxonomy::where('taxonomy', $taxonomy)->pluck('term_id');

        $term     = Term::whereIn('id', $term_ids)->where('name', $term_name)->first();
        $taxonomy = Taxonomy::where('term_id', $term->id)->first();

        return $query->whereHas('taxed', function($q) use($term, $taxonomy) {
            $q->where('taxonomy_id', $taxonomy->id);
        });
    }

    /**
     * Scope by category id.
     *
     * @param  object   $query
     * @param  integer  $taxonomy_id
     * @return mixed
     */
    public function scopeHasCategory($query, $taxonomy_id)
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
    public function scopeHasCategories($query, $taxonomy_ids)
    {
        return $query->whereHas('taxed', function($q) use($taxonomy_ids) {
            $q->whereIn('taxonomy_id', $taxonomy_ids);
        });
    }
}