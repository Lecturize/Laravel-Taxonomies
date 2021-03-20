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
     * @param  string  $taxonomy
     * @param  string  $taxable_class
     * @param  bool    $cached
     * @return Collection
     * @throws Exception
     */
    public static function getTree(string $taxonomy, string $taxable_class = '', bool $cached = true): ?Collection
    {
        $key = "taxonomies.{$taxonomy}.tree";
        $key.= $taxable_class ? '.taxables.'. Str::slug($taxable_class) : '';

        if (! $cached)
            cache()->forget($key);

        return cache()->remember($key, now()->addWeek(), function() use($taxonomy, $taxable_class) {
            $taxonomies = TaxonomyModel::with('parent', 'children')
                                       ->taxonomy($taxonomy)
                                       ->get();

            return self::buildTree($taxonomies, $taxable_class);
        });
    }

    /**
     * Get category tree item.
     *
     * @param  Collection  $taxonomies
     * @param  string      $taxable_class
     * @param  boolean     $is_child
     * @return Collection
     * @throws Exception
     */
    public static function buildTree(Collection $taxonomies, string $taxable_class = '', bool $is_child = false): ?Collection
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
                $key = "taxonomies.{$taxonomy}.{$taxonomy->id}";
                $key.= '.taxables.'. Str::slug($taxable_class) .'.count';

                $item_count = cache()->remember($key, now()->addWeek(), function() use($taxables, $taxable_class) {
                    return $taxables->where('taxable_type', $taxable_class)
                                    ->filter(function ($item) {
                                        // @todo Add dynamic callback.
                                        return $item->whereNull('deleted_at')
                                                    ->where('published_at', '>=', \Carbon\Carbon::now());
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
