[![Latest Stable Version](https://poser.pugx.org/vendocrat/laravel-taxonomies/v/stable)](https://packagist.org/packages/vendocrat/laravel-taxonomies)
[![Total Downloads](https://poser.pugx.org/vendocrat/laravel-taxonomies/downloads)](https://packagist.org/packages/vendocrat/laravel-taxonomies)
[![License](https://poser.pugx.org/vendocrat/laravel-taxonomies/license)](https://packagist.org/packages/vendocrat/laravel-taxonomies)

# Laravel Taxonomies

Simple, nestable Terms & Taxonomies (similar to WordPress) for Laravel 5.

**Attention:** This package is a work in progress, please use with care and be sure to report any issues!

## Installation

Require the package from your `composer.json` file

```php
"require": {
	"vendocrat/laravel-taxonomies": "dev-master"
}
```

and run `$ composer update` or both in one with `$ composer require vendocrat/laravel-taxonomies`.

Next register the service provider and (optional) facade to your `config/app.php` file

```php
'providers' => [
    // Illuminate Providers ...
    // App Providers ...
    vendocrat\Taxonomies\TaxonomiesServiceProvider::class
];
```

```php
'providers' => [
	// Illuminate Facades ...
    'Taxonomy'    => vendocrat\Taxonomies\Facades\Taxonomy::class
];
```

## Configuration & Migration

```bash
$ php artisan vendor:publish --provider="vendocrat\Taxonomies\TaxonomiesServiceProvider"
```

This will create a `config/taxonomies.php` and a migration file. In the config file you can customize the table names, finally you'll have to run migration like so:

```bash
$ php artisan migrate
```

## Usage

##### Add a Term
```php
$model->addTerm( 'My Category', 'taxonomy' )
```

##### Add multiple Terms
```php
$model->addTerm( ['Add','Multiple','Categories'], 'taxonomy' )
```

##### Add a Term with optional parent (taxonomy) & order
```php
$model->addTerm( 'My Category', 'taxonomy', 1, 2 )
```

##### Get all Terms for a model by taxonomy
```php
$model->getTerms( 'taxonomy' )
```

##### Get a specific Term for a model by (optional) taxonomy
```php
$model->getTerm( 'My Category', 'taxonomy' )
```

##### Convenience method for getTerm()
```php
$model->hasTerm( $term, 'taxonomy' )
```

##### Remove a Term from model by (optional) taxonomy
```php
$model->removeTerm( $term, 'taxonomy' )
```

##### Remove all Terms from model
```php
$model->removeAllTerms()
```

##### Scope models with multiple Terms
```php
$model = Model::withTerms( $terms, 'taxonomy' )->get();
```

##### Scope models with one Term
```php
$model = Model::withTerm( $term, 'taxonomy' )->get();
```

## Example

**Add categories to an Eloquent model**

```php
$post = Post::find(1);

$post->addTerm( 'My First Category', 'category' );
$post->addTerm( ['Category Two', 'Category Three'], 'category' );
```

First fo all, this snippet will create three entries in your `terms` table, if they don't already exist:

* My First Category
* Category Two
* Category Three

Then it will create three entries in your `taxonomies` table, relating the terms with the given taxonomy "category".

And last it will relate the entries from your `taxonomies` table with your model (in this example a "Post" model) in your `pivot` table.

**Why three tables?**

Imagine you have a Taxonomy called *post_cat* and another one *product_cat*, the first categorises your blog posts, the second the products in your online shop. Now you add a product to a category (a *term*) called *Shoes* using `$product->addTerm( 'Sheos', 'product_cat' );`. Afterwards you want to blog about that product and add that post to a *post_cat* called *Shoes* as well, using `$product->addTerm( 'Sheos', 'post_cat' );`.

Normally you would have two entries now in your database, one like `['Sheos','product_cat']` and another `['Sheos','post_at']`. Oops, now you recognize you misspelled *Shoes*, now you would have to change it twice, for each Taxonomy.

So I wanted to keep my *Terms* unique throughout my app, which is why I separated them from the Taxonomies and simply related them.

## License

Licensed under [MIT license](http://opensource.org/licenses/MIT).

## Author

**Handcrafted with love by [Alexander Manfred Poellmann](http://twitter.com/AMPoellmann) for [vendocrat](https://vendocr.at) in Vienna &amp; Rome.**