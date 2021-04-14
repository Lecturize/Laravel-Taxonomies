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
     * @param  string|array        $categories
     * @param  string              $taxonomy
     * @param  TaxonomyModel|null  $parent
     * @param  int|null            $sort
     * @return Collection
     */
    public static function createCategories($categories, string $taxonomy, ?TaxonomyModel $parent = null, ?int $sort = null): ?Collection
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
     * @param  string|array  $taxonomy          Either the taxonomy, a taxonomy array or a taxonomy prefix suffixed with % (percent).
     * @param  string        $taxable_class
     * @param  string        $taxable_callback
     * @param  bool          $cached
     * @return Collection
     * @throws Exception
     */
    public static function getTree($taxonomy, string $taxable_class = '', string $taxable_callback = '', bool $cached = true): ?Collection
    {
        $prefix = null;

        if (is_array($taxonomy)) {
            $taxonomy   = array_filter(array_map('trim', $taxonomy));
            $taxonomies = implode('-', $taxonomy);

            $key = "taxonomies.$taxonomies.tree";

        } elseif (is_string($taxonomy) && str_ends_with($taxonomy, '%')) {
            $prefix = str_replace('%', '', $taxonomy);

            $key = "taxonomies.prefixed-$prefix.tree";

        } elseif (is_string($taxonomy)) {
            $key = "taxonomies.$taxonomy.tree";

        } else {
            throw new Exception('The first method argument must be either a string or an array.');
        }

        $key.= $taxable_class ? '.'. Str::slug($taxable_class) : '';
        $key.= $taxable_callback ? '.filter-'. Str::slug($taxable_callback) : '';

        if (! $cached)
            cache()->forget($key);

        return cache()->remember($key, now()->addWeek(), function() use($taxonomy, $prefix, $taxable_class, $taxable_callback) {
            if ($prefix) {
                $taxonomies = TaxonomyModel::with('parent', 'children')
                                           ->taxonomyStartsWith($prefix)
                                           ->get();

            } elseif (is_array($taxonomy)) {
                $taxonomies = TaxonomyModel::with('parent', 'children')
                                           ->taxonomies($taxonomy)
                                           ->get();

            } else {
                $taxonomies = TaxonomyModel::with('parent', 'children')
                                           ->taxonomy($taxonomy)
                                           ->get();
            }

            return self::buildTree($taxonomies, $taxable_class, $taxable_callback);
        });
    }

    /**
     * Get category tree item.
     *
     * @param  Collection  $taxonomies
     * @param  string      $taxable_class
     * @param  string      $taxable_callback
     * @param  boolean     $is_child
     * @return Collection
     * @throws Exception
     */
    public static function buildTree(Collection $taxonomies, string $taxable_class = '', string $taxable_callback = '', bool $is_child = false): ?Collection
    {
        $terms = collect();

        foreach ($taxonomies as $taxonomy) {
            if (! $is_child && ($parent = $taxonomy->parent))
                continue;

            $children_count = 0;

            if ($children = $taxonomy->children) {
                $children = $children->sortBy('sort');

                if (($children_count = $children->count()) > 0)
                    $children = self::buildTree($children, $taxable_class, $taxable_callback, true);
            }

            $item_count = 0;
            if ($taxable_class && ($taxables = $taxonomy->taxables)) {
                $key = "taxonomies.{$taxonomy->id}";
                $key.= '.'. Str::slug($taxable_class);
                $key.= $taxable_callback ? '.filter-'. Str::slug($taxable_callback) : '';
                $key.= '.count';

                $item_count = cache()->remember($key, now()->addWeek(), function() use($taxables, $taxable_class, $taxable_callback) {
                    return $taxables->where('taxable_type', $taxable_class)
                                    ->filter(function ($item) use ($taxable_callback) {
                                        if ($taxable_callback && ($taxable = $item->taxable) && method_exists($taxable, $taxable_callback)) {
                                            try {
                                                return $taxable->{$taxable_callback}();
                                            } catch (Exception $e) {}
                                        }

                                        return true;
                                    })->count();
                });
            }

            $terms->put($taxonomy->term->slug, [
                'uuid'             => $taxonomy->uuid,
                'title'            => $taxonomy->term->title,
                'slug'             => $taxonomy->term->slug,
                'content'          => $taxonomy->content ?? $taxonomy->term->content,
                'lead'             => $taxonomy->lead    ?? $taxonomy->term->lead,
                'sort'             => $taxonomy->sort,
                'alias-params'     => ($alias = $taxonomy->alias) ? $alias->getRouteParameters() : null,
                'children'         => $children_count > 0 ? $children : null,
                'taxable'          => $taxable_class,
                'count'            => $item_count,
                'count-cumulative' => $item_count + ($children ? $children->sum('count-cumulative') : 0),
            ]);
        }

        return $terms;
    }
}
