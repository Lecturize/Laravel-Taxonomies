<?php
return [

    /**
     * Taxonomies
     */
    'taxonomies' => [

        /**
         * Terms.
         */

        'terms' => [
            'table' => 'terms',
            'model' => \Lecturize\Taxonomies\Models\Term::class,
        ],

        /**
         * Taxonomies.
         */

        'taxonomies' => [
            'table' => 'taxonomies',
            'model' => \Lecturize\Taxonomies\Models\Taxonomy::class,
        ],

        /**
         * The "Taxable" pivot.
         */

        'pivot' => [
            'table' => 'taxables',
            'model' => \Lecturize\Taxonomies\Models\Taxable::class,
        ],

    ],

];