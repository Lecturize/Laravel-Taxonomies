<?php namespace Lecturize\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Taxable
 * @package Lecturize\Taxonomies\Models
 * @todo Refactor this into a Pivot Model.
 * @property int       $taxonomy_id
 * @property Taxonomy  $taxonomy
 * @property string    $taxable_type
 * @property int       $taxable_id
 * @property Model     $taxable
 */
class Taxable extends Model
{
    /** @inheritdoc */
    protected $fillable = [
        'taxonomy_id',

        'taxable_type',
        'taxable_id',
    ];

    /** @inheritdoc */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('lecturize.taxonomies.pivot.table','taxables');
    }

    /**
     * The categorized model.
     *
     * @return MorphTo
     */
    public function taxable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The taxonomy.
     *
     * @return BelongsTo
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(
            config('lecturize.taxonomies.taxonomies.model', Taxonomy::class),
            'taxonomy_id',
            'id'
        );
    }
}