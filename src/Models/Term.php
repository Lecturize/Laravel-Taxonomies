<?php namespace vendocrat\Taxonomies\Models;

use Cviebrock\EloquentSluggable\Sluggable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Term extends Model {
	use Sluggable;
	use SoftDeletes;

	/**
	 * @inheritdoc
	 */
	protected $table = 'terms';

	/**
	 * @inheritdoc
	 */
	protected $fillable = [
		'name_de',
		'name_en',
		'name_it',
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
	 * Get Name Attribute for slugging
	 *
	 * @return mixed
	 */
	public function getNameAttribute() {
		if ( $this->name_en ) {
			return $this->name_en;
		} elseif ( $this->name_de ) {
			return $this->name_de;
		} elseif ( $this->name_it ) {
			return $this->name_it;
		}

		return null;
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
				$name = $this->name_en;
				break;

			case 'de' :
				$name = $this->name_de;
				break;

			case 'it' :
				$name = $this->name_it;
				break;
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