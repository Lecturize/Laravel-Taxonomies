<?php namespace Lecturize\Taxonomies;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
     * @param  string|array   $categories
     * @param  string         $taxonomy
     * @param  TaxonomyModel  $parent
     * @param  int            $sort
     * @return Collection
     */
    public static function createCategories($categories, $taxonomy, $parent = null, $sort = null)
    {
        if (is_string($categories))
            $categories = explode('|', $categories);

        $terms      = collect();
        $taxonomies = collect();

        if (count($categories) > 0)
            foreach ($categories as $category)
                $terms->push(Term::firstOrCreate(['title' => $category]));

        foreach ($terms as $term) {
            $tax = TaxonomyModel::firstOrNew([
                'term_id'  => $term->id,
                'taxonomy' => $taxonomy,
            ]);

            if ($tax) {
                if ($parent instanceof TaxonomyModel && $tax->parent_id !== $parent->id)
                    $tax->parent_id = $parent->id;

                if (is_integer($sort) && $tax->sort !== $sort)
                    $tax->sort = $sort;

                $tax->save();

                $taxonomies->push($tax);
            }
        }

        return $taxonomies;
    }

    /**
     * Get the category tree for given taxonomy.
     *
     * @param  string   $taxonomy
     * @param  string   $taxable_class
     * @param  boolean  $cached
     * @return Collection
     * @throws Exception
     */
    public static function getTree(string $taxonomy, $taxable_class = '', $cached = true)
    {
        $key = "taxonomies.{$taxonomy}.tree";
        $key.= $taxable_class ? '.'. Str::slug($taxable_class) : '';

        if (! $cached)
            cache()->forget($key);

        $taxonomies = cache()->remember($key, now()->addWeek(), function() use($taxonomy, $taxable_class) {
            return TaxonomyModel::with('parent', 'children', 'taxables.taxable')
                                ->taxonomy($taxonomy)
                                ->get();
        });

        return self::buildTree($taxonomies, $taxable_class);
    }

    /**
     * Get category tree item.
     *
     * @param  Collection  $taxonomies
     * @param  string      $taxable_class
     * @param  boolean     $is_child
     * @return Collection
     */
    public static function buildTree($taxonomies, $taxable_class = '', $is_child = false)
    {
        $terms = collect();

        foreach ($taxonomies as $taxonomy) {
            if (! $is_child && ($parent = $taxonomy->parent))
                continue;

            $children_count = 0;

            if ($children = $taxonomy->children) {
                $children = $children->sortBy('sort');

                if (($children_count = $children->count()) > 0)
                    $children = self::buildTree($children, $taxable_class, true);
            }

            $item_count = 0;
            if ($taxable_class && ($taxables = $taxonomy->taxables)) {
                $item_count = $taxables->where('taxable_type', $taxable_class)
                                       ->filter(function ($item) {
                                           // @todo Add dynamic callback.
                                           return $item->whereNull('deleted_at')
                                                       ->where('published_at', '>=', \Carbon\Carbon::now());
                                       })->count();
            }

            $terms->put($taxonomy->term->slug, [
                'title'    => $taxonomy->term->title,
                'slug'     => $taxonomy->term->slug,
                'count'    => $item_count,
                'children' => $children_count > 0 ? $children : null,
                'sort'     => $taxonomy->sort,
            ]);
        }

        return $terms;
    }
}
