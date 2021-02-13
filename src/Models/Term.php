<?php namespace Lecturize\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Cviebrock\EloquentSluggable\Sluggable;

/**
 * Class Term
 * @package Lecturize\Taxonomies\Models
 */
class Term extends Model
{
    use Sluggable;
    use SoftDeletes;

    /** @inheritdoc */
    protected $fillable = [
        'title',
        'slug',

        'content',
        'lead',
    ];

    /** @inheritdoc */
    protected $dates = [
        'deleted_at',
    ];

    /** @inheritdoc */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('lecturize.taxonomies.terms.table', 'terms');
    }

    /** @inheritdoc */
    public function sluggable(): array {
        return ['slug' => ['source' => 'title']];
    }

    /**
     * Get the taxonomies (categories) this term belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxonomies() {
        return $this->hasMany(config('lecturize.taxonomies.taxonomies.model', Taxonomy::class));
    }

    /**
     * Fallback attribute for the old column "name".
     * @deprecated Use the title property instead.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->title;
    }

    /**
     * Fallback method for the old column "name".
     * @deprecated Use getDisplayTitle($limit) instead.
     *
     * @param  string  $locale
     * @param  int     $limit
     * @return mixed
     */
    public function getDisplayName($locale = '', $limit = 0)
    {
        return $this->getDisplayTitle($limit);
    }

    /**
     * Get display title.
     *
     * @param  int     $limit
     * @return mixed
     */
    public function getDisplayTitle($limit = 0)
    {
        return $limit > 0 ? Str::slug($this->title, $limit) : $this->title;
    }
}
