<?php namespace Lecturize\Taxonomies\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Cviebrock\EloquentSluggable\Sluggable;

/**
 * Class Term
 * @package Lecturize\Taxonomies\Models
 * @property string       $title
 * @property string|null  $slug
 * @property string|null  $content
 * @property string|null  $lead
 * @property Collection   $taxonomies
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
     * @return HasMany
     */
    public function taxonomies(): HasMany
    {
        return $this->hasMany(config('lecturize.taxonomies.taxonomies.model', Taxonomy::class));
    }

    /**
     * Fallback attribute for the old column "name".
     * @deprecated Use the title property instead.
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->title;
    }

    /**
     * Fallback method for the old column "name".
     * @deprecated Use getDisplayTitle($limit) instead.
     *
     * @param  string  $locale
     * @param  int     $limit
     * @return string
     */
    public function getDisplayName($locale = '', $limit = 0): string
    {
        return $this->getDisplayTitle($limit);
    }

    /**
     * Get display title.
     *
     * @param  int     $limit
     * @return string
     */
    public function getDisplayTitle($limit = 0): string
    {
        return $limit > 0 ? Str::slug($this->title, $limit) : $this->title;
    }
}
