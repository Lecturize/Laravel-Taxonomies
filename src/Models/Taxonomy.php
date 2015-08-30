<?php namespace vendocrat\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Taxonomy extends Model
{
	use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'taxonomies';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'term_id',
		'taxonomy',
		'desc',
		'parent',
		'order',
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function term() {
		return $this->belongsTo('vendocrat\Taxonomies\Models\Term');
	}

	/**
	 * TODO
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
	 */
	/*
	public function posts()
	{
		return $this->morphedByMany('App\vendocrat\Models\Posts\Post', 'taxable', 'taxable');
	}
	*/

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function parent()
	{
		return $this->belongsTo('vendocrat\Taxonomies\Models\Taxonomy', 'parent');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function children()
	{
		return $this->hasMany('vendocrat\Taxonomies\Models\Taxonomy', 'parent');
	}

	/**
	 * @param $query
	 * @param string $taxonomy
	 * @return mixed
	 */
	public function scopeTaxonomy( $query, $taxonomy )
	{
		return $query->where( 'taxonomy', '=', $taxonomy );
	}

	/**
	 * @param $query
	 * @param string $term
	 * @param string $taxonomy
	 * @return mixed
	 */
	public function scopeTerm( $query, $term, $taxonomy = 'major' )
	{
		return $query->whereHas('term', function($q) use($term, $taxonomy) {
			$q->where( 'name', '=', $term );
		});
	}

	/**
	 * @param $query
	 * @param string $searchTerm
	 * @param string $taxonomy
	 * @return mixed
	 */
	public function scopeSearch( $query, $searchTerm, $taxonomy = 'major' )
	{
		return $query->whereHas('term', function($q) use($searchTerm, $taxonomy) {
			$q->where( 'name', 'like', '%'. $searchTerm .'%' );
		});
	}

}
