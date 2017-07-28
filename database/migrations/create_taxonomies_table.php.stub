<?php

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
     * @var string  $table_terms       The terms table name.
     * @var string  $table_taxonomies  The taxonomies table name.
     * @var string  $table_pivot       The pivot table name.
     */
    protected $table_terms;
    protected $table_taxonomies;
    protected $table_pivot;

    /**
     * Create a new migration instance.
     */
    public function __construct()
    {
        $this->table_terms      = config('lecturize.taxonomies.table_terms',      'terms');
        $this->table_taxonomies = config('lecturize.taxonomies.table_taxonomies', 'taxonomies');
        $this->table_pivot      = config('lecturize.taxonomies.table_pivot',      'taxables');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table_terms, function(Blueprint $table)
        {
            $table->increments('id');

            $table->string('name')->nullable()->unique();
            $table->string('slug')->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create($this->table_taxonomies, function(Blueprint $table)
        {
	        $table->increments('id');

            $table->integer('term_id')
                  ->nullable()
                  ->unsigned()
                  ->references('id')
                  ->on($this->table_terms)
                  ->onDelete('cascade');

            $table->string('taxonomy')->default('default');
            $table->string('desc')->nullable();

            $table->integer('parent')->unsigned()->default(0);

            $table->smallInteger('sort')->unsigned()->default(0);

            $table->timestamps();
            $table->softDeletes();

	        $table->unique(['term_id', 'taxonomy']);
        });

        Schema::create($this->table_pivot, function(Blueprint $table)
        {
            $table->integer('taxonomy_id')
                  ->nullable()
                  ->unsigned()
                  ->references('id')
                  ->on($this->table_taxonomies);

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
        Schema::dropIfExists($this->table_pivot);
        Schema::dropIfExists($this->table_taxonomies);
        Schema::dropIfExists($this->table_terms);
    }
}