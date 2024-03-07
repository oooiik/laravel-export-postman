<?php

namespace Oooiik\LaravelExportPostman;

use Illuminate\Support\ServiceProvider;
use Oooiik\LaravelExportPostman\Commands\ExportPostmanCommand;

class ExportPostmanServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
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