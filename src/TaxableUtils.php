<?php namespace vendocrat\Taxonomies;

use vendocrat\Taxonomies\Models\Taxonomy;
use vendocrat\Taxonomies\Models\Term;

class TaxableUtils
{

	public function createTaxables( $terms, $taxonomy, $parent = 0, $order = 0 )
	{

		$terms = $this->makeTermsArray($terms);

		$this->createTerms( $terms );
		$this->createTaxonomies( $terms, $taxonomy, $parent, $order );

	}

	public static function createTerms( array $terms )
	{
		if ( count($terms) === 0 )
			return;

		$found = Term::whereIn( 'name', $terms )->lists('name')->all();

		if ( ! is_array($found) )
			$found = array();

		foreach ( array_diff( $terms, $found ) as $term ) {
			Term::firstOrCreate([ 'name' => $term ]);
		}
	}

	public static function createTaxonomies( array $terms, $taxonomy, $parent = 0, $order = 0 )
	{
		if ( count($terms) === 0 )
			return;

		// only keep terms with existing entries in terms table
		$terms = Term::whereIn( 'name', $terms )->lists('name')->all();

		// create taxonomy entries for given terms
		foreach ( $terms as $term ) {
			Taxonomy::firstOrCreate([
				'taxonomy' => $taxonomy,
				'term_id'  => Term::where( 'name', $term )->first()->id,
				'parent'   => $parent,
				'order'    => $order,
			]);
		}
	}

	/**
	 * @param string|array $terms
	 * @return array
	 */
	public static function makeTermsArray( $terms ) {
		if ( is_array($terms) ) {
			return $terms;
		} else if ( is_string($terms) ) {
			return explode( '|', $terms );
		}

		return (array) $terms;
	}

}