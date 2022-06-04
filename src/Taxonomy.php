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
    protected Application $app;

    public function __construct(Application $app)
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
    public static function createCategories(string|array $categories, string $taxonomy, ?TaxonomyModel $parent = null, ?int $sort = null): Collection
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
     * @param  string        $taxable_relation  A relationship method on a custom Taxonomy model, if a class is given we'll try to guess a relationship method of it.
     * @param  string        $taxable_callback
     * @param  bool          $cached
     * @return Collection
     * @throws Exception
     */
    public static function getTree(string|array $taxonomy, string $taxable_relation = '', string $taxable_callback = '', bool $cached = true): Collection
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

        $key.= $taxable_relation ? '.'. Str::slug($taxable_relation) : '';
        $key.= $taxable_callback ? '.filter-'. Str::slug($taxable_callback) : '';

        if (! $cached)
            cache()->forget($key);

        return maybe_tagged_cache(['taxonomies', 'taxonomies:tree'])->remember($key, config('lecturize.taxonomies.cache-expiry', now()->addWeek()), function() use($taxonomy, $prefix, $taxable_relation, $taxable_callback) {
            $taxonomy_model = app(config('lecturize.taxonomies.taxonomies.model', Taxonomy::class));

            if ($prefix) {
                $taxonomies = $taxonomy_model::with('parent', 'children')
                                             ->taxonomyStartsWith($prefix)
                                             ->get();

            } elseif (is_array($taxonomy)) {
                $taxonomies = $taxonomy_model::with('parent', 'children')
                                             ->taxonomies($taxonomy)
                                             ->get();

            } else {
                $taxonomies = $taxonomy_model::with('parent', 'children')
                                             ->taxonomy($taxonomy)
                                             ->get();
            }

            return self::buildTree($taxonomies, $taxable_relation, $taxable_callback);
        });
    }

    /**
     * Get category tree item.
     *
     * @param  Collection  $taxonomies
     * @param  string      $taxable_relation
     * @param  string      $taxable_callback
     * @param  boolean     $is_child
     * @return Collection
     * @throws Exception
     */
    public static function buildTree(Collection $taxonomies, string $taxable_relation = '', string $taxable_callback = '', bool $is_child = false): Collection
    {
        $terms = collect();

        $relation = '';

        if ($taxable_relation) {
            if (str_contains($taxable_relation, '\\')) {
                $relation = strtolower(substr($taxable_relation, strrpos($taxable_relation, '\\') + 1));
                $relation = Str::plural($relation);
            } else {
                $relation = $taxable_relation;
            }

            $taxonomies->load($relation);
        }

        foreach ($taxonomies->sortBy('sort') as $taxonomy) {
            if (! $is_child && ! is_null($taxonomy->parent_id))
                continue;

            $children_count = 0;

            if ($children = $taxonomy->children) {
                if (($children_count = $children->count()) > 0) {
                    $children->load('parent', 'children');
                    $children = self::buildTree($children, $taxable_relation, $taxable_callback, true);
                }
            }

            $item_count = 0;

            if ($relation && method_exists($taxonomy, $relation) && ($taxables = $taxonomy->{$relation})) {
                $key = "taxonomies.$taxonomy->id";
                $key.= '.'. Str::slug($relation);
                $key.= $taxable_callback ? '.filter-'. Str::slug($taxable_callback) : '';
                $key.= '.count';

                $item_count = maybe_tagged_cache(['taxonomies', 'taxonomies:tree'])->remember($key, config('lecturize.taxonomies.cache-expiry', now()->addWeek()), function() use($taxables, $taxable_callback) {
                    return $taxables->filter(function ($item) use ($taxable_callback) {
                                        if ($taxable_callback && method_exists($item, $taxable_callback)) {
                                            try {
                                                return $item->{$taxable_callback}();
                                            } catch (Exception) {}
                                        }

                                        return true;
                                    })->count();
                });
            }

            $terms->put($taxonomy->term->slug, [
                'uuid'             => $taxonomy->uuid,
                'taxonomy'         => $taxonomy->taxonomy,
                'title'            => $taxonomy->term->title,
                'slug'             => $taxonomy->term->slug,
                'content'          => $taxonomy->content   ?? $taxonomy->term->content,
                'lead'             => $taxonomy->lead      ?? $taxonomy->term->lead,
                'meta_desc'        => $taxonomy->meta_desc ?? $taxonomy->lead ?? $taxonomy->term->lead,
                'sort'             => $taxonomy->sort,
                'visible'          => $taxonomy->visible,
                'searchable'       => $taxonomy->searchable,
                'alias-params'     => ($alias = $taxonomy->alias) ? $alias->getRouteParameters() : null,
                'children'         => $children_count > 0 ? $children : null,
                'taxable'          => $relation,
                'count'            => $item_count,
                'count-cumulative' => $item_count + ($children ? $children->sum('count-cumulative') : 0),
            ]);
        }

        return $terms;
    }
}
