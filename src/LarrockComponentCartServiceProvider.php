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
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'larrock');
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/../views', 'larrock');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../views' => base_path('resources/views/vendor/larrock')
        ]);
        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/larrock')
        ], 'lang');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('larrockcart', function() {
            $class = config('larrock.components.cart', CartComponent::class);
            return new $class;
        });
    }
}