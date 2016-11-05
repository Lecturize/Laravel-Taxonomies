<?php namespace Lecturize\Taxonomies\Models;

use Cviebrock\EloquentSluggable\Sluggable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Term
 * @package Lecturize\Taxonomies\Models
 */
class Term extends Model
{
	use Sluggable;
	use SoftDeletes;

	/**
	 * @inheritdoc
	 */
	protected $table = 'terms';

	/**
     * @todo make this editable via config file
	 * @inheritdoc
	 */
	protected $fillable = [
		'name',
		'slug',
	];

	/**
	 * @inheritdoc
	 */
	protected $dates = ['deleted_at'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function taxable() {
		return $this->morphMany(Taxable::class, 'taxable');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function taxonomies() {
		return $this->hasMany(Taxonomy::class);
	}

	/**
	 * Get Display Name
	 *
	 * @param  string $locale
	 * @param  int    $limit
	 * @return mixed
	 */
	public function getDisplayName( $locale = '', $limit = 0 ) {
		$locale = $locale ?: app()->getLocale();

		switch ( $locale ) {
			case 'en' :
			default :
				$name = $this->name;
				break;
/*
			case 'de' :
				$name = $this->name_de;
				break;

			case 'it' :
				$name = $this->name_it;
				break;
*/
		}

		return $limit > 0 ? str_limit($name, $limit) : $name;
	}

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
}