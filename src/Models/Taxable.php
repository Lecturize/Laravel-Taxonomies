<?php namespace vendocrat\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;

class Taxable extends Model
{
	/**
	 * @inheritdoc
	 */
	protected $table = 'taxables';

	/**
	 * @inheritdoc
	 */
	protected $fillable = [
		'taxonomy_id',
		'taxable_id',
		'taxable_type'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function taxable()
	{
		return $this->morphTo();
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function taxonomy()
	{
		return $this->belongsTo(Taxonomy::class, 'taxonomy_id', 'id');
	}

}