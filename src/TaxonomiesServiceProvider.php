<?php namespace Lecturize\Taxonomies;

use Illuminate\Support\ServiceProvider;

class TaxonomiesServiceProvider extends ServiceProvider
{
    protected $migrations = [
        'CreateTaxonomiesTable'          => 'create_taxonomies_table',
        'ExtendTaxonomiesTables'         => 'extend_taxonomies_tables',
        'AddVisibilityToTaxonomiesTable' => 'add_visibility_to_taxonomies_table',
        'AddMetaDescToTaxonomiesTable'   => 'add_meta_desc_to_taxonomies_table'
    ];

     public function boot()
     {
        $this->handleConfig();
        $this->handleMigrations();

         $this->loadTranslationsFrom(__DIR__ .'/../resources/lang', 'taxonomies');
     }

    /** @inheritdoc */
     public function register()
     {
         $this->app->singleton('taxonomies', function ($app) {
             return new Taxonomy($app);
         });
     }

    /** @inheritdoc */
     public function provides()
     {
          return [];
     }

    private function handleConfig(): void
    {
        $configPath = __DIR__ . '/../config/config.php';

        $this->publishes([$configPath => config_path('lecturize.php')]);

        $this->mergeConfigFrom($configPath, 'lecturize');
    }

    private function handleMigrations(): void
    {
        $count = 0;
        foreach ($this->migrations as $class => $file) {
            if (! class_exists($class)) {
                $timestamp = date('Y_m_d_Hi'. sprintf('%02d', $count), time());

                $this->publishes([
                    __DIR__ .'/../database/migrations/'. $file .'.php.stub' =>
                        database_path('migrations/'. $timestamp .'_'. $file .'.php')
                ], 'migrations');

                $count++;
            }
        }
    }
}