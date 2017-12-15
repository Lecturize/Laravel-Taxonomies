<?php namespace Lecturize\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Taxable
 * @package Lecturize\Taxonomies\Models
 */
class Taxable extends Model
{
    /**
     * @inheritdoc
     */
    protected $fillable = [
        'taxonomy_id',
        'taxable_id',
        'taxable_type'
    ];

    /**
     * @inheritdoc
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('lecturize.taxonomies.table_pivot','taxables');
    }

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