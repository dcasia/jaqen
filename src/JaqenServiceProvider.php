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

        $services = [
            ScaffoldServiceProvider::class,
            ResourceManagerServiceProvider::class,
        ];

        foreach ($services as $service) {
            $this->app->register($service);
        }

        $this->app->singleton(Jaqen::class, fn() => new Jaqen($this));
    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => 'jaqen-api',
        ];
    }

    public function resources(): array
    {
        return [];
    }

}
