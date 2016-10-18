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

		if ( ! class_exists('CreateTaxonomiesTable') ) {
			$timestamp = date('Y_m_d_His', time());

			$this->publishes([
				__DIR__ .'/../database/migrations/create_taxonomies_table.stub' =>
					database_path('migrations/'. $timestamp .'_create_taxonomies_table.php')
			], 'migrations');
		}
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
