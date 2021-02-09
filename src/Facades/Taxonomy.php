<?php namespace Lecturize\Taxonomies\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Taxonomy
 * @package Lecturize\Taxonomies\Facades
 *
 * @method \Illuminate\Support\Collection createCategories()
 */
class Taxonomy extends Facade
{
     /** @inheritdoc */
     protected static function getFacadeAccessor()
     {
          return 'taxonomies';
     }
}