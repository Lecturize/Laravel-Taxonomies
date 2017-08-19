<?php namespace Lecturize\Taxonomies\Facades;

use Illuminate\Support\Facades\Facade;

class Taxonomy extends Facade
{
     /**
     * @inheritdoc
      */
     protected static function getFacadeAccessor()
     {
          return 'taxonomies';
     }
}