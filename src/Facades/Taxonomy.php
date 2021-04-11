<?php namespace Lecturize\Taxonomies\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

use Lecturize\Taxonomies\Models\Taxonomy as TaxonomyModel;

/**
 * Class Taxonomy
 * @package Lecturize\Taxonomies\Facades
 *
 * @method static Collection createCategories(string|array $categories, string $taxonomy, TaxonomyModel $parent = null, int $sort = null)
 * @method static Collection getTree(string $taxonomy, string $taxable_class = '', boolean $cached = true)
 * @method static Collection buildTree( Collection $taxonomies, string $taxable_class, boolean $is_child = false)
 */
class Taxonomy extends Facade
{
     /** @inheritdoc */
     protected static function getFacadeAccessor(): string
     {
          return 'taxonomies';
     }
}