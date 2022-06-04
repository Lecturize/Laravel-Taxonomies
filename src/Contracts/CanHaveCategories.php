<?php namespace Lecturize\Taxonomies\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Interface CanHaveCategories
 * @package Lecturize\Taxonomies\Contracts
 */
interface CanHaveCategories
{
    /** @return MorphToMany */
    public function taxonomies(): MorphToMany;
}