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

Coming soon!

## Example

Coming soon!

## License

Licensed under [MIT license](http://opensource.org/licenses/MIT).

## Author

**Handcrafted with love by [Alexander Manfred Poellmann](http://twitter.com/AMPoellmann) for [vendocrat](https://vendocr.at) in Vienna &amp; Rome.**