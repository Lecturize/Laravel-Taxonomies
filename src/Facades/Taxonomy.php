<?php namespace Lecturize\Taxonomies\Facades;

use Illuminate\Support\Facades\Facade;
use Lecturize\Taxonomies\Models\Taxonomy as TaxonomyModel;

/**
 * Class Taxonomy
 * @package Lecturize\Taxonomies\Facades
 *
 * @method static \Illuminate\Support\Collection createCategories(string|array $categories, string $taxonomy, TaxonomyModel $parent = null, int $sort = null)
 */
class Taxonomy extends Facade
{
     /** @inheritdoc */
     protected static function getFacadeAccessor()
     {
          return 'taxonomies';
     }
}