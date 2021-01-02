<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen;

use DigitalCreative\Jaqen\Services\AbstractService;
use DigitalCreative\Jaqen\Services\Crud\CrudServiceProvider;
use DigitalCreative\Jaqen\Services\Scaffold\SidebarService;
use DigitalCreative\Jaqen\Services\Scaffold\ScaffoldServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class JaqenServiceProvider extends ServiceProvider
{

    abstract public function resources(): array;

    public function services(): array
    {
        return [];
    }

    private array $defaultServices = [
        SidebarService::class,
    ];

    private function resolveServices(): array
    {
        return array_merge($this->defaultServices, $this->services());
    }

    public function boot(): void
    {

        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });

        $this->app->register(ScaffoldServiceProvider::class);
        $this->app->register(CrudServiceProvider::class);

        $this->app->singleton(Jaqen::class, function () {
            return (new Jaqen($this))->setResources($this->resources());
        });

    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => 'jaqen-api',
        ];
    }

}
