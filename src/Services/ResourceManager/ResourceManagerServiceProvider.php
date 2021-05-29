<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager;

use Carbon\Laravel\ServiceProvider;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\ResourceController;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\FiltersController;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\FieldsController;
use DigitalCreative\Jaqen\Jaqen;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\DeleteController;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\DetailController;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\IndexController;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\StoreController;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\UpdateController;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class ResourceManagerServiceProvider extends ServiceProvider implements DeferrableProvider
{

    public function boot(): void
    {

        $this->app->singleton(ResourceManager::class, function () {

            return (new ResourceManager())->setResources(
                Jaqen::getInstance()->invokeProviderMethod('resources')
            );

        });

        Route::get('/jaqen-api/resources', [ ResourceController::class, 'resources' ])->name('jaqen.resources');

        Route::group([ 'prefix' => '/jaqen-api', 'as' => 'jaqen.resource.' ], function (Router $router) {

            $router->get('/resource/{resource}/filters', [ FiltersController::class, 'filters' ])->name('filters');
            $router->get('/resource/{resource}/fields', [ FieldsController::class, 'fields' ])->name('fields');

            $router->get('/resource/{resource}/{key}', [ DetailController::class, 'handle' ])->name('show');
            $router->patch('/resource/{resource}/{key}', [ UpdateController::class, 'handle' ])->name('update');
            $router->delete('/resource/{resource}', [ DeleteController::class, 'handle' ])->name('destroy');
            $router->post('/resource/{resource}', [ StoreController::class, 'handle' ])->name('store');
            $router->get('/resource/{resource}', [ IndexController::class, 'handle' ])->name('index');

        });
    }

    public function provides(): array
    {
        return [
            ResourceManager::class,
        ];
    }

}
