[![Latest Stable Version](https://poser.pugx.org/vendocrat/laravel-taxonomies/v/stable)](https://packagist.org/packages/vendocrat/laravel-taxonomies)
[![Total Downloads](https://poser.pugx.org/vendocrat/laravel-taxonomies/downloads)](https://packagist.org/packages/vendocrat/laravel-taxonomies)
[![License](https://poser.pugx.org/vendocrat/laravel-taxonomies/license)](https://packagist.org/packages/vendocrat/laravel-taxonomies)

# Laravel Taxonomies

Simple, nestable Terms & Taxonomies for Laravel 5.

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
    'Setting' => vendocrat\Taxonomies\Facades\Taxonomies::class
];
```

## Configuration & Migration

Laravel Taxonomies includes an optional config file. Get started buy publishing it:

```bash
$ php artisan vendor:publish --provider="vendocrat\Taxonomies\TaxonomiesServiceProvider"
```

This will create a `config/sample.php` file and a migration file. Afterwards you'll have to run the artisan migrate command:

```bash
$ php artisan migrate
```

## Usage



## Example



## To-Dos



## License

Licensed under [MIT license](http://opensource.org/licenses/MIT).

## Author

**Handcrafted with love by [Alexander Manfred Poellmann](http://twitter.com/AMPoellmann) for [vendocrat](https://vendocr.at) in Vienna &amp; Rome.**