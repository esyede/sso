<?php

namespace Esyede\SSO;

use Illuminate\Support\ServiceProvider;
use Esyede\SSO\Commands;
use Esyede\SSO\Controllers\ServerController;

class SSOServiceProvider extends ServiceProvider
{
    protected $configFileName = 'sso.php';

    public function boot()
    {
        $this->publishConfig(__DIR__ . '/../config/' . $this->configFileName);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\CreateBroker::class,
                Commands\DeleteBroker::class,
                Commands\ListBrokers::class,
            ]);
        }

        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make(ServerController::class);
    }

    protected function getConfigPath()
    {
        return config_path($this->configFileName);
    }

    protected function publishConfig(string $configPath)
    {
        $this->publishes([$configPath => $this->getConfigPath()]);
    }

    protected function loadRoutes()
    {
        if (config('sso.type') === 'server') {
            $this->loadRoutesFrom(__DIR__ . '/Routes/server.php');
        }
    }
}
