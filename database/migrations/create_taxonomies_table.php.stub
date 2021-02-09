<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class TaxonomiesTable
 */
class CreateTaxonomiesTable extends Migration
{
    /**
     * Table names.
     *
     * @var string  $terms       The terms table name.
     * @var string  $taxonomies  The taxonomies table name.
     * @var string  $pivot       The pivot table name.
     */
    protected $terms;
    protected $taxonomies;
    protected $pivot;

    /**
     * Create a new migration instance.
     */
    public function __construct()
    {
        $this->terms      = config('lecturize.taxonomies.terms.table',      config('lecturize.taxonomies.terms_table',      'terms'));
        $this->taxonomies = config('lecturize.taxonomies.taxonomies.table', config('lecturize.taxonomies.taxonomies_table', 'taxonomies'));
        $this->pivot      = config('lecturize.taxonomies.pivot.table',      config('lecturize.taxonomies.pivot_table',      'taxables'));
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->terms, function(Blueprint $table)
        {
            $table->increments('id');

            $table->string('name')->nullable()->unique();
            $table->string('slug')->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create($this->taxonomies, function(Blueprint $table)
        {
             $table->increments('id');

            $table->integer('term_id')
                  ->nullable()
                  ->unsigned()
                  ->references('id')
                  ->on($this->terms)
                  ->onDelete('cascade');

            $table->string('taxonomy')->default('default');
            $table->string('desc')->nullable();

            $table->integer('parent')->unsigned()->default(0);

            $table->smallInteger('sort')->unsigned()->default(0);

            $table->timestamps();
            $table->softDeletes();

             $table->unique(['term_id', 'taxonomy']);
        });

        Schema::create($this->pivot, function(Blueprint $table)
        {
            $table->integer('taxonomy_id')
                  ->nullable()
                  ->unsigned()
                  ->references('id')
                  ->on($this->taxonomies);

            $table->nullableMorphs('taxable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->pivot);
        Schema::dropIfExists($this->taxonomies);
        Schema::dropIfExists($this->terms);
    }
}