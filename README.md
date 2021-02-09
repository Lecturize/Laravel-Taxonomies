[![Latest Stable Version](https://poser.pugx.org/lecturize/laravel-taxonomies/v/stable)](https://packagist.org/packages/lecturize/laravel-taxonomies)
[![Total Downloads](https://poser.pugx.org/lecturize/laravel-taxonomies/downloads)](https://packagist.org/packages/lecturize/laravel-taxonomies)
[![License](https://poser.pugx.org/lecturize/laravel-taxonomies/license)](https://packagist.org/packages/lecturize/laravel-taxonomies)

# Laravel Taxonomies

Simple, nestable Terms & Taxonomies (similar to WordPress) for Laravel.

## Installation

Require the package from your `composer.json` file

```php
"require": {
    "lecturize/laravel-taxonomies": "^1.0"
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

First, add our `HasTaxonomies` trait to your model.
        
```php
<?php namespace App\Models;

use Lecturize\Taxonomies\Traits\HasTaxonomies;

class Post extends Model
{
    use HasTaxonomies;

    // ...
}
?>
```

##### Add a Term
```php
$model->addTerm('My Category', 'taxonomy')
```

##### Add multiple Terms
```php
$model->addTerm(['Add','Multiple','Categories'], 'taxonomy')
```

##### Add a Term with optional parent (taxonomy) & order
```php
$model->addTerm('My Category', 'taxonomy', 1, 2)
```

##### Get all Terms for a model by taxonomy
```php
$model->getTerms('taxonomy')
```

##### Get a specific Term for a model by (optional) taxonomy
```php
$model->getTerm('My Category', 'taxonomy')
```

##### Convenience method for getTerm()
```php
$model->hasTerm($term, 'taxonomy')
```

##### Remove a Term from model by (optional) taxonomy
```php
$model->removeTerm($term, 'taxonomy')
```

##### Remove all Terms from model
```php
$model->removeAllTerms()
```

##### Scope models with multiple Terms
```php
$model = Model::withTerms($terms, 'taxonomy')->get();
```

##### Scope models with one Term
```php
$model = Model::withTerm($term, 'taxonomy')->get();
```

## Example

**Add categories to an Eloquent model**

```php
$post = Post::find(1);

$post->addTerm('My First Category', 'category');
$post->addTerm(['Category Two', 'Category Three'], 'category');
```

First fo all, this snippet will create three entries in your `terms` table, if they don't already exist:

* My First Category
* Category Two
* Category Three

Then it will create three entries in your `taxonomies` table, relating the terms with the given taxonomy "category".

And last it will relate the entries from your `taxonomies` table with your model (in this example a "Post" model) in your `pivot` table.

**Why three tables?**

Imagine you have a Taxonomy called *post_cat* and another one *product_cat*, the first categorises your blog posts, the second the products in your online shop. Now you add a product to a category (a *term*) called *Shoes* using `$product->addTerm('Shoes', 'product_cat');`. Afterwards you want to blog about that product and add that post to a *post_cat* called *Shoes* as well, using `$product->addTerm('Shoes', 'post_cat');`.

Normally you would have two entries now in your database, one like `['Shoes','product_cat']` and another `['Shoes','post_at']`. Oops, now you recognize you misspelled *Shoes*, now you would have to change it twice, for each Taxonomy.

So I wanted to keep my *Terms* unique throughout my app, which is why I separated them from the Taxonomies and simply related them.

## Changelog

- [2021-02-09] **v1.0** Extended the database tables to support UUIDs (be sure to generate some on your existing models) and better customization.

## License

Licensed under [MIT license](http://opensource.org/licenses/MIT).

## Author

**Handcrafted with love by [Alexander Manfred Poellmann](https://twitter.com/AMPoellmann) in Vienna &amp; Rome.**