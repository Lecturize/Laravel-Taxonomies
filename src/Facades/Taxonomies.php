<?php namespace vendocrat\Taxonomies\Facades;

use vendocrat\Taxonomies\Taxonomies as TaxonomiesAccessor;
use Illuminate\Support\Facades\Facade;

class Taxonomies extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return TaxonomiesAccessor::class;
	}
}