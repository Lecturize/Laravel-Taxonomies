<?php namespace vendocrat\Taxonomies\Models;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Term extends Model implements
	SluggableInterface
{
	use SluggableTrait;
	use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'terms';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'slug',
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
	 * The validation rules for this model.
	 *
	 * @var array
	 */
	protected $validationRules = [
		'name' => 'required',
		'slug' => 'required',
	];

	/**
	 * Sluggable
	 *
	 * @var array
	 */
	protected $sluggable = [
		'build_from' => 'name',
		'save_to'    => 'slug',
	];

	public function taxable() {
		return $this->morphMany('vendocrat\Taxonomies\Models\Taxable', 'taxable');
	}

	public function taxonomies() {
		return $this->hasMany('vendocrat\Taxonomies\Models\Taxonomy');
	}

}
