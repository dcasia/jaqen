<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen;

use DigitalCreative\Jaqen\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CoreJaqenServiceProvider extends ServiceProvider
{

    public function boot(): void
    {

        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'jaqen');
        $this->routes();

    }

    public function registerPublishing(): void
    {

        $this->publishes([
            __DIR__ . '/Console/stubs/JaqenServiceProvider.stub' => app_path('Providers/JaqenServiceProvider.php'),
        ], 'jaqen-provider');

        $this->publishes([ __DIR__ . '/../config/config.php' => config_path('jaqen-ui.php') ], 'config');

    }

    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::prefix('jaqen')
             ->get('/{view?}', [ MainController::class, 'index' ])
             ->where('view', '.*');
    }

}
