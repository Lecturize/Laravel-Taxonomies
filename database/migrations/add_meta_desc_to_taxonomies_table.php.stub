<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaDescToTaxonomiesTable extends Migration
{
    protected string $taxonomies;

    public function __construct()
    {
        $this->taxonomies = config('lecturize.taxonomies.taxonomies.table', config('lecturize.taxonomies.taxonomies_table', 'taxonomies'));
    }

    public function up(): void
    {
        Schema::table($this->taxonomies, function(Blueprint $table) {
            $table->text('meta_desc')->nullable()->after('lead');
        });
    }

    public function down(): void
    {
        Schema::table($this->taxonomies, function(Blueprint $table) {
            $table->dropColumn('meta_desc');
        });
    }
}