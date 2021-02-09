<?php namespace Lecturize\Taxonomies;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;

use Lecturize\Taxonomies\Models\Taxonomy as TaxonomyModel;
use Lecturize\Taxonomies\Models\Term;

/**
 * Class Taxonomy
 * @package Lecturize\Taxonomies
 */
class Taxonomy
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Create a new Cache manager instance.
     *
     * @param  Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Creates terms and taxonomies.
     *
     * @param  string|array  $categories
     * @param  string        $taxonomy
     * @param  int           $parent_id
     * @param  int           $sort
     * @return Collection
     */
    public static function createCategories($categories, $taxonomy, $parent_id = null, $sort = null)
    {
        if (is_string($categories))
            $categories = explode('|', $categories);

        $terms = $taxonomies = collect();

        if (count($categories) > 0)
            foreach ($categories as $category)
                $terms->push(Term::firstOrCreate(['title' => $category]));

        foreach ($terms as $term) {
            $tax = TaxonomyModel::firstOrCreate([
                'term_id'  => $term->id,
                'taxonomy' => $taxonomy,
            ]);

            $parent = TaxonomyModel::where('id', $parent_id)->first();

            if ($tax) {
                if ($tax->parent_id !== $parent_id)
                    $tax->parent()->associate($parent);

                if ($tax->sort !== $sort)
                    $tax->update(['sort' => $sort]);
            }

            $taxonomies->push($tax);
        }

        return $taxonomies;
    }
}