<?php

namespace Vendor\AyonApi\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            // ajoute ici tes providers si nécessaire
            \Vendor\AyonApi\AyonServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // configuration spécifique pour tests
        $app['config']->set('services.ayon.hostname', 'localhost:5000');
        $app['config']->set('services.ayon.username', 'admin');
        $app['config']->set('services.ayon.password', 'admin');
    }
}
