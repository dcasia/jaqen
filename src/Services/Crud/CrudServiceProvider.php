<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Crud;

use Carbon\Laravel\ServiceProvider;
use DigitalCreative\Jaqen\Services\Crud\Http\Controllers\DeleteController;
use DigitalCreative\Jaqen\Services\Crud\Http\Controllers\DetailController;
use DigitalCreative\Jaqen\Services\Crud\Http\Controllers\IndexController;
use DigitalCreative\Jaqen\Services\Crud\Http\Controllers\StoreController;
use DigitalCreative\Jaqen\Services\Crud\Http\Controllers\UpdateController;
use DigitalCreative\Jaqen\Services\Scaffold\Http\Controllers\ScaffoldController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class CrudServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::group([ 'prefix' => '/jaqen-api' ], function (Router $router) {
            $router->get('/crud/{resource}/{key}', [ DetailController::class, 'handle' ]);
            $router->patch('/crud/{resource}/{key}', [ UpdateController::class, 'handle' ]);
            $router->delete('/crud/{resource}', [ DeleteController::class, 'handle' ]);
            $router->post('/crud/{resource}', [ StoreController::class, 'handle' ]);
            $router->get('/crud/{resource}', [ IndexController::class, 'handle' ]);
        });
    }
}
