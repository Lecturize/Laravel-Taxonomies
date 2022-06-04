<?php
return [

    /**
     * Taxonomies
     */
    'taxonomies' => [

        /*
         * Terms.
         */

        'terms' => [
            'table' => 'terms',
            'model' => \Lecturize\Taxonomies\Models\Term::class,
        ],

        /*
         * Taxonomies.
         */

        'taxonomies' => [
            'table' => 'taxonomies',
            'model' => \Lecturize\Taxonomies\Models\Taxonomy::class,
        ],

        /*
         * The "Taxable" pivot.
         */

        'pivot' => [
            'table' => 'taxables',
        ],

        /*
         * Cache settings.
         */

        'cache-expiry' => null, // set to null to use package default (one week)

    ],

];