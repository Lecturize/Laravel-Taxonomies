<?php
return [
    /*
     * Taxonomies
     */
    'taxonomies' => [
        /*
         * Terms table
         */
        'table_terms' => 'terms',
        
        /*
         * Terms model
         */
        'model_term' => Lecturize\Taxonomies\Models\Term::class,

        /*
         * Taxonomies table
         */
        'table_taxonomies' => 'taxonomies',
        
        /*
         * Taxonomies model
         */
        'model_taxonomy' => Lecturize\Taxonomies\Models\Taxonomy::class,

        /*
         * Relationship table
         */
        'table_pivot' => 'taxables',
        
        /*
         * Relationship model
         */
        'model_pivot' => Lecturize\Taxonomies\Models\Taxable::class,
        
    ],
];