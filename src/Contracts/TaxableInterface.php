<?php namespace Lecturize\Taxonomies\Contracts;

interface TaxableInterface
{
    /**
     * @return mixed
     */
     public function taxed();

    /**
     * @return mixed
     */
     public function taxonomies();
}