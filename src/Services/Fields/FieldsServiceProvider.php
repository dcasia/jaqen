<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Fields;

use Carbon\Laravel\ServiceProvider;
use DigitalCreative\Jaqen\Services\Fields\Http\Controllers\BelongsToController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class FieldsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /**
         * Relationship
         */
        Route::group([ 'prefix' => '/jaqen-api/fields', 'as' => 'jaqen.fields.' ], function (Router $router) {
            $router->get('/belongs-to/{resource}/{field}', [ BelongsToController::class, 'searchBelongsTo' ])->name('belongs-to');
        });
    }
}
