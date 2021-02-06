<?php namespace Lecturize\Taxonomies\Traits;

use Lecturize\Taxonomies\Models\Taxonomy;
use Lecturize\Taxonomies\Models\Term;

/**
 * Class ModelFinder
 * @package Lecturize\Taxonomies\Traits
 */
trait ModelFinder
{
    /**
     * Find term.
     *
     * @param  string  $slug
     * @return Term
     */
    public function findTerm($slug): Term
    {
        return Term::whereSlug($slug)->first();
    }

    /**
     * Find taxonomy by term.
     *
     * @param  string  $term_id
     * @param  string  $taxonomy
     * @return Taxonomy
     */
    public function findTaxonomyByTerm($term_id, $taxonomy): Taxonomy
    {
        return Taxonomy::where('term_id', $term_id)
                       ->where('taxonomy', $taxonomy)
                       ->first();
    }
}