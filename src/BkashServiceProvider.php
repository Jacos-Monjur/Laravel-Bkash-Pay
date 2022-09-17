<?php

namespace monjur\bkash;

use Illuminate\Support\ServiceProvider;

class BkashServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/views', 'bkash');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        // $this->registerSeedsFrom(__DIR__ . '/../database/seeders');
        $this->mergeConfigFrom(__DIR__ . '/config/bkashpay.json', 'bkash');
        $this->publishes([
            __DIR__ . '/config/bkashpay.json' => config_path('bkashpay.json'),
            __DIR__ . '/resources/views' => resource_path('views/Bkash')
        ]);
    }

    public function register()
    {
    }
}
