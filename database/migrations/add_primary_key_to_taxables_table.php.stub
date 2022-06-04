<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimaryKeyToTaxablesTable extends Migration
{
    protected string $pivot;

    public function __construct()
    {
        $this->pivot = config('lecturize.taxonomies.pivot.table', config('lecturize.taxonomies.pivot_table', 'taxables'));
    }

    public function up(): void
    {
        Schema::table($this->pivot, function(Blueprint $table) {
            $table->primary(['taxonomy_id', 'taxable_type', 'taxable_id']);
        });
    }

    public function down(): void
    {
        Schema::table($this->pivot, function(Blueprint $table) {
            $table->dropPrimary(['taxonomy_id', 'taxable_type', 'taxable_id']);
        });
    }
}