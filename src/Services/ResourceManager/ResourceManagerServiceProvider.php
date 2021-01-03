<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager;

use Carbon\Laravel\ServiceProvider;
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

        Route::group([ 'prefix' => '/jaqen-api' ], function (Router $router) {

            $router->get('/resource/{resource}/fields', [ FieldsController::class, 'fields' ]);

            $router->get('/resource/{resource}/{key}', [ DetailController::class, 'handle' ]);
            $router->patch('/resource/{resource}/{key}', [ UpdateController::class, 'handle' ]);
            $router->delete('/resource/{resource}', [ DeleteController::class, 'handle' ]);
            $router->post('/resource/{resource}', [ StoreController::class, 'handle' ]);
            $router->get('/resource/{resource}', [ IndexController::class, 'handle' ]);

        });
    }

    public function provides(): array
    {
        return [
            ResourceManager::class,
        ];
    }
}
