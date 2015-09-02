<?php namespace vendocrat\Taxonomies;

trait TaxableTrait
{
	/**
	 * Return collection of taxonomies related to the taxed model
	 */
	public function taxed()
	{
		return $this->morphMany('vendocrat\Taxonomies\Models\Taxable', 'taxable');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function taxonomies()
	{
		return $this->morphToMany('vendocrat\Taxonomies\Models\Taxonomy', 'taxable', 'taxable');
	}

	/**
	 * @param $terms
	 * @param $taxonomy
	 * @param int $parent
	 * @param int $order
	 */
	public function addTerm( $terms, $taxonomy, $parent = 0, $order = 0 )
	{
		$terms = TaxableUtils::makeTermsArray($terms);

		$this->createTaxables( $terms, $taxonomy, $parent, $order );

		$terms = Term::whereIn( 'name', $terms )->lists('id')->all();

		if ( count($terms) > 0 ) {
			foreach ( $terms as $term )
			{
				if ( $this->taxonomies()->where('taxonomy', $taxonomy)->where('term_id', $term)->first() )
					continue;

				$tax = Taxonomy::where( 'term_id', $term )->first();
				$this->taxonomies()->attach($tax->id);
			}

			return;
		}

		$this->taxonomies()->detach();
	}











	/**
	 * @param $taxonomy_id
	 */
	public function setCategory( $taxonomy_id )
	{
		$this->taxonomies()->attach($taxonomy_id);
	}

	/*
	public function setCategory( $term_id, $taxonomy = 'category' )
	{
		$taxable = Taxonomy::firstOrCreate([
				'taxonomy' => $taxonomy,
				'term_id'  => $term_id
			]);

		$this->taxonomies()->attach($taxable->id);
	}
	*/






	/**
	 * @param $terms
	 * @param $taxonomy
	 * @param int $parent
	 * @param int $order
	 */
	public function createTaxables( $terms, $taxonomy, $parent = 0, $order = 0 )
	{
		$terms = TaxableUtils::makeTermsArray($terms);

		TaxableUtils::createTerms( $terms );
		TaxableUtils::createTaxonomies( $terms, $taxonomy, $parent, $order );
	}

	/**
	 * @param string $by
	 * @return mixed
	 */
	public function getTaxonomies( $by = 'id' )
	{
		return $this->taxonomies->lists( $by );
	}

	/**
	 * @param string $taxonomy
	 * @return mixed
	 */
	public function getTerms( $taxonomy = '' )
	{
		if ( $taxonomy ) {
			$term_ids = $this->taxonomies->where( 'taxonomy', $taxonomy )->lists('term_id');

		} else {
			$term_ids = $this->getTaxonomies( 'term_id' );
		}

		return Term::whereIn( 'id', $term_ids )->lists('name');
	}

	/**
	 * @param $term
	 * @param string $taxonomy
	 * @return mixed
	 */
	public function getTerm( $term, $taxonomy = '' )
	{
		if ( $taxonomy ) {
			$term_ids = $this->taxonomies->where( 'taxonomy', $taxonomy )->lists('term_id');
		} else {
			$term_ids = $this->getTaxonomies( 'term_id' );
		}

		return Term::whereIn( 'id', $term_ids )->where( 'name', '=', $term )->first();
	}

	/**
	 * @param $term
	 * @param string $taxonomy
	 * @return bool
	 */
	public function hasTerm( $term, $taxonomy = '' )
	{
		return (bool) $this->getTerm($term, $taxonomy);
	}

	/**
	 * @param $term
	 * @param string $taxonomy
	 * @return mixed
	 */
	public function removeTerm( $term, $taxonomy = '' )
	{
		if ( $term = $this->getTerm($term, $taxonomy) ) {
			if ( $taxonomy ) {
				$taxonomy = $this->taxonomies->where('taxonomy', $taxonomy)->where('term_id', $term->id)->first();
			} else {
				$taxonomy = $this->taxonomies->where('term_id', $term->id)->first();
			}

			return $this->taxed()->where('taxonomy_id', $taxonomy->id)->delete();
		}

		return null;
	}

	/**
	 * @return mixed
	 */
	public function removeAllTerms()
	{
		return $this->taxed()->delete();
	}

	/**
	 * Filter model to subset with the given tags
	 *
	 * @param object $query
	 * @param array $terms
	 * @param string $taxonomy
	 * @return object $query
	 */
	public function scopeWithTerms( $query, $terms, $taxonomy )
	{
		$terms = TaxableUtils::makeTermsArray($terms);

		foreach ( $terms as $term ) {
			$this->scopeWithTerm($query, $term, $taxonomy);
		}

		return $query;
	}

	/**
	 * Filter model to subset with the given tags
	 *
	 * @param object $query
	 * @param string $term
	 * @param string $taxonomy
	 * @return
	 */
	public function scopeWithTax( $query, $term, $taxonomy ) {
		$term_ids = Taxonomy::where( 'taxonomy', $taxonomy )->lists('term_id');

		$term = Term::whereIn( 'id', $term_ids )->where( 'name', '=', $term )->first();

		$taxonomy = Taxonomy::where( 'term_id', $term->id )->first();

		return $query->whereHas('taxed', function($q) use($term, $taxonomy) {
			$q->where('taxonomy_id', $taxonomy->id);
		});
	}

	public function scopeWithTerm( $query, $term, $taxonomy ) {
		$term_ids = Taxonomy::where( 'taxonomy', $taxonomy )->lists('term_id');

		$term = Term::whereIn( 'id', $term_ids )->where( 'name', '=', $term )->first();

		$taxonomy = Taxonomy::where( 'term_id', $term->id )->first();

		return $query->whereHas('taxonomies', function($q) use($term, $taxonomy) {
			$q->where('term_id', $term->id);
		});
	}

	public function scopeHasCategory( $query, $taxonomy_id ) {
		return $query->whereHas('taxed', function($q) use($taxonomy_id) {
			$q->where('taxonomy_id', $taxonomy_id);
		});
	}
}