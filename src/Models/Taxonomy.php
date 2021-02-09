<?php namespace Lecturize\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Taxonomy
 * @package Lecturize\Taxonomies\Models
 */
class Taxonomy extends Model
{
    use SoftDeletes;

    /** @inheritdoc */
    protected $fillable = [
        'parent_id',
        'term_id',
        'taxonomy',

        'description',
        'content',
        'lead',

        'order_by',
        'order_direction',
        'sort',

        'properties',
    ];

    /** @inheritdoc */
    protected $casts = [
        'properties' => 'array',
    ];

    /** @inheritdoc */
    protected $dates = [
        'deleted_at'
    ];

    /** @inheritdoc */
    protected $with = [
        'term'
    ];

    /** @inheritdoc */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('lecturize.taxonomies.taxonomies.table', 'taxonomies');
    }

    /** @inheritdoc */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->getConnection()
                      ->getSchemaBuilder()
                      ->hasColumn($model->getTable(), 'uuid'))
                $model->uuid = \Webpatser\Uuid\Uuid::generate()->string;
        });
    }

    /**
     * Get the term, that will be displayed as this taxonomies (categories) title.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term() {
        return $this->belongsTo(config('lecturize.taxonomies.terms.model', Term::class));
    }

    /**
     * Get the parent taxonomy (categories).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(config('lecturize.taxonomies.taxonomies.model', Taxonomy::class));
    }

    /**
     * Get the children taxonomies (categories).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(config('lecturize.taxonomies.taxonomies.model', Taxonomy::class));
    }

    /**
     * An example for related posts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function posts()
    {
        return $this->morphedByMany(config('lecturize.community.posts.model', 'App\Models\Posts\Post'), 'taxable', 'taxables');
    }

    /**
     * An example for related products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function products()
    {
        return $this->morphedByMany(config('lecturize.shop.products.model', 'Lecturize\Shop\Products\Product'), 'taxable', 'taxables');
    }

    /**
     * Scope by a given taxonomy (e.g. "blog_cat" for blog posts or "shop_cat" for shop products).
     *
     * @param  object  $query
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeTaxonomy($query, $taxonomy)
    {
        return $query->where('taxonomy', $taxonomy);
    }

    /**
     * Scope terms (category title) by given taxonomy.
     *
     * @param  object      $query
     * @param  string|int  $term
     * @param  string      $term_field
     * @return mixed
     */
    public function scopeTerm($query, $term, $term_field = 'title')
    {
        $term_field = ! in_array($term_field, ['id', 'title', 'slug']) ? 'title' : $term_field;

        return $query->whereHas('term', function($q) use($term, $term_field) {
            $q->where($term_field, $term);
        });
    }

    /**
     * A simple search scope.
     *
     * @param  object  $query
     * @param  string  $term
     * @param  string  $taxonomy
     * @return mixed
     */
    public function scopeSearch($query, $term, $taxonomy)
    {
        return $query->whereHas('term', function($q) use($term, $taxonomy) {
            $q->where('title', 'like', '%'. $term .'%');
        });
    }
}