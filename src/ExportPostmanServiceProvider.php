<?php

namespace Oooiik\LaravelExportPostman;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Oooiik\LaravelExportPostman\Commands\ExportPostmanCommand;
use Oooiik\LaravelExportPostman\Helper\Helper;
use Oooiik\LaravelExportPostman\Helper\HelperInterface;

class ExportPostmanServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(HelperInterface::class, function (Application $app) {
            return $app->make(Helper::class);
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/export-postman.php' => config_path('export-postman.php'),
            ], 'postman-config');
        }

        $this->commands(ExportPostmanCommand::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/export-postman.php', 'export-postman');
    }
}