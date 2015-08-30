<?php namespace vendocrat\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;

class Taxable extends Model
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'taxable';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'taxonomy_id',
		'taxable_id',
		'taxable_type'
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
	protected $dates = [];

	public function taxable()
	{
		return $this->morphTo();
	}

	public function taxonomy()
	{
		return $this->belongsTo('vendocrat\Taxonomies\Models\Taxonomy', 'taxonomy_id', 'id');
	}

}