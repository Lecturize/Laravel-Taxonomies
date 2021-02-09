<?php namespace Lecturize\Taxonomies;

use Lecturize\Taxonomies\Models\Taxonomy;
use Lecturize\Taxonomies\Models\Term;

/**
 * Class TaxableUtils
 * @package Lecturize\Taxonomies
 */
class TaxableUtils
{
    /**
     * @param mixed   $terms
     * @param string  $taxonomy
     * @param int     $parent_id
     * @param int     $sort
     */
    public function createCategoryItems($terms, $taxonomy, $parent_id = null, $sort = null)
    {
        $terms = $this->makeTermsArray($terms);

        $this->createTerms($terms);
        $this->createTaxonomies($terms, $taxonomy, $parent_id, $sort);
    }

    /**
     * @deprecated Use createCategoryItems() instead.
     *
     * @param mixed   $terms
     * @param string  $taxonomy
     * @param int     $parent_id
     * @param int     $sort
     */
    public function createTaxables($terms, $taxonomy, $parent_id = null, $sort = null)
    {
        $this->createCategoryItems($terms, $taxonomy, $parent_id, $sort);
    }

    /**
     * @param array $terms
     */
    public static function createTerms(array $terms)
    {
        if (count($terms) > 0) {
            $found = Term::whereIn('title', $terms)->pluck('title')->all();

            if (! is_array($found))
                $found = [];

            foreach (array_diff($terms, $found) as $title) {
                if (Term::where('title', $title)->first())
                    continue;

                $term = Term::create([
                    'title' => $title,
                ]);
            }
        }
    }

    /**
     * @param array   $terms
     * @param string  $taxonomy
     * @param int     $parent_id
     * @param int     $sort
     */
    public static function createTaxonomies(array $terms, $taxonomy, $parent_id = null, $sort = null)
    {
        if (count($terms) > 0) {
            // only keep terms with existing entries in terms table
            $terms = Term::whereIn('title', $terms)->get();

            // create taxonomy entries for given terms
            foreach ($terms as $term) {
                if ($found = Taxonomy::taxonomy($taxonomy)->term($term->id, 'id')->where('parent_id', $parent_id)->first()) {
                    if ($found->sort !== $sort)
                        $found->update(['sort' => $sort]);

                    continue;
                }

                $model = Taxonomy::create([
                    'taxonomy' => $taxonomy,
                    'term_id'  => $term->id,
                    'sort'     => $sort,
                ]);

                if ($parent = Taxonomy::where('id', $parent_id)->first())
                    $model->parent()->associate($parent);
            }
        }
    }

     /**
      * Return the given terms as an array.
      *
      * @param  string|array  $terms
      * @return array
      */
     public static function makeTermsArray($terms) {
          if (is_array($terms)) {
               return $terms;
          } elseif (is_string($terms)) {
               return explode('|', $terms);
          }

          return (array) $terms;
     }
}