<?php

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Lecturize\Taxonomies\Taxonomy;

/**
 * Get categories tree as a collection.
 *
 * @param  string|array  $taxonomy
 * @param  string        $route
 * @param  string        $taxable
 * @param  string        $taxable_callback
 * @param  bool          $include_empty
 * @return Collection
 * @throws Exception
 */
function get_categories_collection($taxonomy = 'category', string $route = '', string $taxable = '', string $taxable_callback = '', bool $include_empty = false): Collection {
    $tree = Taxonomy::getTree($taxonomy, $taxable, $taxable_callback);

    return build_categories_collection_from_tree($tree, $taxonomy, $route, $taxable, $include_empty);
}

/**
 * Recursive function to build categories collection from given taxonomies tree.
 *
 * @param  Collection    $tree
 * @param  string|array  $taxonomy
 * @param  string        $route
 * @param  mixed         $taxable
 * @param  bool          $include_empty
 * @param  array         $params
 * @param  array         $attributes
 * @param  bool          $is_child
 * @return Collection
 */
function build_categories_collection_from_tree(Collection $tree, $taxonomy, string $route, string $taxable, bool $include_empty = false, array $params = [], array $attributes = [], bool $is_child = false): Collection {
    $temp  = $params;
    $items = collect();

    $count = 1;
    foreach ($tree as $slug => $properties) {
        array_push($params, $properties['slug']);

        $children = null;

        foreach ($properties as $key => $value) {
            if ($value instanceof Collection) {
                $children = build_categories_collection_from_tree($value, $taxonomy, $route, $taxable, $include_empty, $params, $attributes, true);
                break;
            } else {
                $children = null;
            }
        }

        $is_active = is_active_route($route, $params);

        $item = [
            'title'            => $properties['title'],
            'slug'             => $properties['slug'],
            'route'            => $route,
            'params'           => is_array($properties['alias-params']) ? get_term_link($route, $properties['alias-params']) : $params,
            'link'             => is_array($properties['alias-params']) ? get_term_link($route, $properties['alias-params']) : get_term_link($route, $params),
            'children'         => $children,
            'count'            => $properties['count'],
            'count-cumulative' => $properties['count-cumulative'],
            'active'           => $is_active,
            'active-branch'    => $is_active ?: ($children && $children->where('active-branch', true)->count()),
        ];

        $params = [];
        if ($count !== count($tree))
            $params = $temp;

        $count++;

        if (! $include_empty && $properties['count-cumulative'] < 1) {
             continue;
        } else {
            $items->push($item);
        }
    }

    return $items;
}

/**
 * Get the categories for a given model.
 *
 * @param  Model   $model
 * @param  string  $taxonomy
 * @param  string  $route
 * @return array
 */
function get_categories_for_model(Model $model, string $taxonomy = 'category', string $route = 'taxonomy.show'): array {
    if (! method_exists($model, 'taxonomies'))
        return [];

    if (! $taxonomies = $model->taxonomies)
        return [];

    if ($taxonomy)
        $taxonomies = $taxonomies->where('taxonomy', $taxonomy);

    $categories = [];
    foreach ($taxonomies as $taxonomy) {
        $params = $taxonomy->getRouteParameters();
        $params = array_diff($params, [$taxonomy->taxonomy]);

        $categories[] = '<a href="'. get_term_link($route, $params) .'" rel="tag">'. $taxonomy->term->title .'</a>';
    }

    return $categories;
}

/**
 * Get category options for a select box.
 *
 * @param  mixed   $categories
 * @param  string  $selected_slug
 * @param  int     $level
 * @return string
 */
function get_category_options($categories, string $selected_slug = '', int $level = 0): string {
    $category_items = [];

    foreach ($categories as $item) {
        $children     = $item['children'];
        $has_children = $children && count($children) > 0;

        $category_item = '<option class="level-'. $level .'" value="'. $item['slug'] .'"'. ($selected_slug === $item['slug'] ? ' selected' : '') .'>';
        $category_item.= trim(str_repeat('> ', $level) .' '. $item['title']);
        $category_item.= '</option>';

        if ($has_children) {
            $category_children = get_category_options($children, $selected_slug, $level + 1);

            $category_item.= $category_children;
        }

        if ($level === 0)
            $category_item = '<optgroup label="'. $item['title'] .' ('. $item['taxonomy'] .')">'. $category_item .'</optgroup>';

        $category_items[] = $category_item;
    }

    return implode('', array_filter(array_map('trim', $category_items)));
}

/**
 * Get a terms link.
 *
 * @param  string  $route
 * @param  array   $params
 * @return string
 */
function get_term_link(string $route = 'taxonomy.show', array $params = []): string {
    return $route ? route($route, $params) : '#';
}

if (! function_exists('is_active_route')) :
    /**
     * Check if given route is the active route.
     *
     * @param  string  $route
     * @param  array   $params
     * @return bool
     */
    function is_active_route(string $route = '', array $params = []): bool {
        if (is_array($params) && count($params) > 0)
            return is_active_path($route, $params);

        if (request()->routeIs($route))
            return true;

        if (! $currentRoute = get_current_route())
            return false;

        if (! is_array($route))
            $route = [$route];

        if (in_array($currentRoute, $route))
            return true;

        return false;
    }
endif;

if (! function_exists('get_current_route')) :
    /**
     * Shortcut to get current route.
     *
     * @return string
     */
    function get_current_route(): string {
        return app()->router->currentRouteName();
    }
endif;

if (! function_exists('is_active_path')) :
    /**
     * Check if current path matches url generated from the given route and route parameters.
     *
     * @param  string  $route
     * @param  array   $params
     * @return bool
     */
    function is_active_path(string $route = '', array $params = []): bool {
        $route = route($route, $params, false);
        $path  = '/'. request()->decodedPath();

        return $route === $path;
    }
endif;