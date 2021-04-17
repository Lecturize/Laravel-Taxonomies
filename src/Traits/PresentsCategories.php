<?php namespace Lecturize\Taxonomies\Traits;

use Exception;
use Illuminate\Http\RedirectResponse;
use Lecturize\Taxonomies\Models\Taxonomy;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PresentsCategories
 * @package Lecturize\Taxonomies\Traits
 */
trait PresentsCategories
{
    /**
     * Check taxonomy path.
     *
     * @param  Taxonomy|null  $current
     * @param  string|null    $route
     * @return null|RedirectResponse
     * @throws Exception
     */
    protected function checkTaxonomyPath(Taxonomy $current, string $route = ''): ?RedirectResponse
    {
        $link = route($route, $current->getRouteParameters());

        $components = parse_url($link);

        $path = ltrim($components['path'] ?? null, '/\\');

        // if there is no path, throw an exception
        if (empty($path))
            throw new Exception();

        // the link path and the request path don't match, so we redirect
        if ($path !== request()->decodedPath())
            return redirect($link);

        return null;
    }

    /**
     * Check taxonomy hierarchy.
     *
     * @param  string|null    $slug
     * @param  Taxonomy|null  $current
     * @param  Taxonomy|null  $parent
     * @param  string|null    $route
     * @return null|RedirectResponse
     * @throws Exception
     * @throws NotFoundHttpException
     */
    protected function checkTaxonomyHierarchy(?string $slug, ?Taxonomy $current, ?Taxonomy $parent = null, string $route = ''): ?RedirectResponse
    {
        // if there is no slug given, do nothing
        if (empty($slug))
            return null;

        // no taxonomy has been found for the given slug
        // let's redirect to parent if given or abort
        if (! $current)
            if ($parent) {
                return redirect()->route($route, $parent->getRouteParameters());
            } else {
                throw new NotFoundHttpException();
            }

        // a taxonomy has been found, see if it matches the given parent
        // redirect to calculated destination
        if ($current->parent_id !== ($parent ? $parent->id : null))
            return redirect()->route($route, $current->getRouteParameters());

        return null;
    }
}
