<?php

namespace Vendor\AyonApi;

use Illuminate\Support\ServiceProvider;

class AyonServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AyonService::class, function ($app) {
            return new AyonService();
        });
    }

    public function boot()
    {
        // Optionnel: publier config
        $this->publishes([
            __DIR__.'/config/ayon.php' => config_path('ayon.php'),
        ], 'config');
    }
}
