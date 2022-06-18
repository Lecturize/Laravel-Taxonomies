[![Latest Stable Version](https://poser.pugx.org/lecturize/laravel-taxonomies/v/stable)](https://packagist.org/packages/lecturize/laravel-taxonomies)
[![Total Downloads](https://poser.pugx.org/lecturize/laravel-taxonomies/downloads)](https://packagist.org/packages/lecturize/laravel-taxonomies)
[![License](https://poser.pugx.org/lecturize/laravel-taxonomies/license)](https://packagist.org/packages/lecturize/laravel-taxonomies)

# Laravel Taxonomies

Simple, nestable Terms & Taxonomies (similar to WordPress) for Laravel.

## Installation

Require the package from your `composer.json` file

```php
"require": {
    "lecturize/laravel-taxonomies": "^1.2"
}
```

and run `$ composer update` or both in one with `$ composer require lecturize/laravel-taxonomies`.

## Configuration & Migration

```bash
$ php artisan vendor:publish --provider="Cviebrock\EloquentSluggable\ServiceProvider"
$ php artisan vendor:publish --provider="Lecturize\Taxonomies\TaxonomiesServiceProvider"
```

This will publish a `config/sluggable.php`, a `config/lecturize.php` and some migration files, that you'll have to run:

```bash
$ php artisan migrate
```

For migrations to be properly published ensure that you have added the directory `database/migrations` to the classmap in your projects `composer.json`.

## Usage

First, add our `HasCategories` trait to your model.
        
```php
<?php

namespace App\Models;

use Lecturize\Taxonomies\Contracts\CanHaveCategories;
use Lecturize\Taxonomies\Traits\HasCategories;

class Post extends Model implements CanHaveCategories
{
    use HasCategories;

    // ...
}
?>
```

##### Add a Term
```php
$model->addCategory('My Category', 'blog_category')
```

##### Add multiple Terms
```php
$model->addCategories(['Add','Multiple','Categories'], 'blog_category')
```

##### Add a Term with optional parent_id (taxonomy->id) & sort order
```php
$model->addCategory('My Category', 'blog_category', 1, 2)
```

##### Get all Terms for a model by taxonomy
```php
$model->getCategories('taxonomy')
```

##### Get a specific Term for a model by (optional) taxonomy
```php
$model->getCategory('My Category', 'blog_category')
```

##### See if model has a given category within given taxonomy
```php
$model->hasCategory('My Category', 'blog_category')
```

##### Remove a Term from model by (optional) taxonomy
```php
$model->removeCategory('My Category', 'blog_category')
```

##### Remove (detach) all categories relations from model
```php
$model->detachCategories()
```

##### Scope models with any of the given categories
```php
$model = Model::categorizedIn(['Add','Multiple','Categories'], 'blog_category')->get();
```

##### Scope models with one category
```php
$model = Model::categorized('My Category', 'blog_category')->get();
```

## Helper functions

I've included a set of helper functions for your convenience, see `src/helpers.php`.

## Custom taxonomy model

Make sure to create a custom `Taxonomy` model, that extends `Lecturize\Taxonomies\Models\Taxonomy`.

```php
<?php

namespace App\Models;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Lecturize\Taxonomies\Models\Taxonomy as TaxonomyBase;

class Taxonomy extends TaxonomyBase
{
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'taxable', 'taxables')
                    ->withTimestamps();
    }
}
```

Don't forget to publish the config file and update the value of `lecturize.taxonomies.taxonomies.model` to `\App\Models\Post:class`.

## Example

**Add categories to an Eloquent model**

```php
$post = Post::find(1);

$post->addCategory('My First Category', 'blog_category');
$post->addCategories(['Category Two', 'Category Three'], 'blog_category');
```

First, this snippet will create three entries in your `terms` table, if they don't already exist:

* My First Category
* Category Two
* Category Three

Then it will create three entries in your `taxonomies` table, relating the terms with the given taxonomy "category".

And last it will relate the entries from your `taxonomies` table with your model (in this example a "Post" model) in your `pivot` table.

**Why three tables?**

Imagine you have a Taxonomy called *post_cat* and another one *product_cat*, the first categorises your blog posts, the second the products in your online shop. Now you add a product to a category (a *term*) called *Shoes* using `$product->addCategory('Shoes', 'product_cat');`. Afterwards you want to blog about that product and add that post to a *post_cat* called *Shoes* as well, using `$product->addCategory('Shoes', 'post_cat');`.

Normally you would have two entries now in your database, one like `['Shoes','product_cat']` and another `['Shoes','post_at']`. Oops, now you recognize you misspelled *Shoes*, now you would have to change it twice, for each Taxonomy.

So I wanted to keep my *Terms* unique throughout my app, which is why I separated them from the Taxonomies and simply related them.

## Changelog

### [2021-02-09] **v1.0**

Extended the database tables to support UUIDs (be sure to generate some on your existing models) and better customization. Quite some breaking changes throughout the whole package.

### [2022-05-16] **v1.1**

Updated dependencies to PHP 8 and Laravel 8/9 - for older versions please refer to v1.0. Added new columns like `content`, `lead`, `meta_desc`, `visible` and `searchable` to taxonomies table. Renamed the `term` scope on the `Taxonomy` model to `byTerm` to avoid confusion with its `term` relationship method.

### [2022-06-04] **v1.2**

Removed the `Taxable` model, you should create a custom `Taxonomy` model in your project that extends `Lecturize\Taxonomies\Models\Taxonomy` and contain your project-specific **morphedByMany** relations, e.g. `posts`. Don't forget to override the config value of `config('lecturize.taxonomies.taxonomies.model')` accordingly.

On the `taxable` pivot table a primary key has been added, make sure you have no duplicates in that table **before** publishing and running the new migrations! Also, timestamps have been added to the pivot table.

If you are using the `get_categories_collection()` or `build_categories_collection_from_tree()` helper function or `Taxonomy::getTree()` review the slightly adapted signatures. Instead of a taxables class (e.g. `\Post:class`) through the `$taxable` argument, we now ask for a `$taxable_relation` (e.g. the `posts` relation on your custom taxonomy model). If you continue to pass a class name like `\Post:class` we'll try and guess the relation by using `Str::plural()`. 

## License

Licensed under [MIT license](http://opensource.org/licenses/MIT).

## Author

**Handcrafted with love by [Alexander Manfred Poellmann](https://twitter.com/AMPoellmann) in Vienna &amp; Rome.**