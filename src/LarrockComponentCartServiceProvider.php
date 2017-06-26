<?php

namespace Larrock\ComponentCart;

use Illuminate\Support\ServiceProvider;

class LarrockComponentCartServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'larrock');

        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/larrock'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
        $this->app->make(CartComponent::class);

        $timestamp = date('Y_m_d_His', time());
        $migrations = [];
        if ( !class_exists('CreateLarrockCartTable')){
            $migrations = [__DIR__.'/../database/migrations/0000_00_00_000000_create_cart_table.php' => database_path('migrations/'.$timestamp.'_create_cart_table.php')];
        }
        if ( !class_exists('AddForeignKeysToLarrockCartTable')){
            $migrations = [__DIR__.'/../database/migrations/0000_00_00_000000_add_foreign_keys_to_cart_table.php' => database_path('migrations/'.$timestamp.'_add_foreign_keys_to_cart_table.php')];
        }

        $this->publishes([
            $migrations
        ], 'migrations');
    }
}
