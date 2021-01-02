<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen;

use DigitalCreative\Jaqen\Services\AbstractService;
use DigitalCreative\Jaqen\Services\ResourceManager\ResourceManagerServiceProvider;
use DigitalCreative\Jaqen\Services\Scaffold\ScaffoldServiceProvider;
use DigitalCreative\Jaqen\Services\Scaffold\SidebarService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class JaqenServiceProvider extends ServiceProvider
{

    public function boot(): void
    {

        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });

        $this->app->register(ScaffoldServiceProvider::class);
        $this->app->register(ResourceManagerServiceProvider::class);

        $this->app->singleton(Jaqen::class, function () {
            return new Jaqen($this);
        });

    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => 'jaqen-api',
        ];
    }

}
