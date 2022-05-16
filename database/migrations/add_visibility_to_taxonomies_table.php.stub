<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibilityToTaxonomiesTable extends Migration
{
    protected string $taxonomies;

    public function __construct()
    {
        $this->taxonomies = config('lecturize.taxonomies.taxonomies.table', config('lecturize.taxonomies.taxonomies_table', 'taxonomies'));
    }

    public function up(): void
    {
        Schema::table($this->taxonomies, function(Blueprint $table) {
            $table->boolean('visible')->default(1)->after('sort');
            $table->boolean('searchable')->default(1)->after('visible');
        });
    }

    public function down(): void
    {
        Schema::table($this->taxonomies, function(Blueprint $table) {
            $table->dropColumn('visible');
            $table->dropColumn('searchable');
        });
    }
}