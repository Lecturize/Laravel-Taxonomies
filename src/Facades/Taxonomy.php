<?php namespace Lecturize\Taxonomies\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Taxonomy
 * @package Lecturize\Taxonomies\Facades
 *
 * @method static \Illuminate\Support\Collection createCategories(string|array $categories, string $taxonomy, int $parent_id = null, int $sort = null)
 */
class Taxonomy extends Facade
{
     /** @inheritdoc */
     protected static function getFacadeAccessor()
     {
          return 'taxonomies';
     }
}