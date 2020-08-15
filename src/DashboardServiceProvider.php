<?php

namespace DigitalCreative\Dashboard;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class DashboardServiceProvider extends ServiceProvider
{

    abstract public function resources(): array;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });

        $this->app->singleton(Dashboard::class, function () {
            return (new Dashboard())
                ->setResources($this->resources());
        });

    }

    /**
     * Get the Nova route group configuration array.
     *
     * @return array
     */
    protected function routeConfiguration(): array
    {
        return [
            'prefix' => 'dashboard-api',
        ];
    }

}
