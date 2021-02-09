<?php namespace Lecturize\Taxonomies\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Taxonomy
 * @package Lecturize\Taxonomies\Facades
 */
class Taxonomy extends Facade
{
     /** @inheritdoc */
     protected static function getFacadeAccessor()
     {
          return 'taxonomies';
     }
}