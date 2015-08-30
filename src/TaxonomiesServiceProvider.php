<?php namespace vendocrat\Taxonomies;

use Illuminate\Support\ServiceProvider;

class TaxonomiesServiceProvider extends ServiceProvider
{
	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__ .'/../config/config.php' => config_path('taxonomies.php')
		], 'config');

		$this->publishes([
			__DIR__ .'/../database/migrations/' => database_path('migrations')
		], 'migrations');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__ .'/../config/config.php',
			'taxonomies'
		);

		$this->app->singleton(Taxonomies::class, function ($app) {
			return new Taxonomies($app);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return string[]
	 */
	public function provides()
	{
		return [
			Taxonomies::class
		];
	}
}
