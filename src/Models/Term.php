<?php

namespace Lecturize\Taxonomies\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Cviebrock\EloquentSluggable\Sluggable;

/**
 * Class Term
 * @package Lecturize\Taxonomies\Models
 * @property int                    $id
 * @property string                 $title
 * @property string|null            $slug
 * @property string|null            $content
 * @property string|null            $lead
 * @property Collection|Taxonomy[]  $taxonomies
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
     * Get display title.
     *
     * @param  int  $limit
     * @return string
     */
    public function getDisplayTitle(int $limit = 0): string
    {
        return $limit > 0 ? Str::slug($this->title, $limit) : $this->title;
    }
}
