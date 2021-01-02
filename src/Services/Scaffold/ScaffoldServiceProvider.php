<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Scaffold;

use Carbon\Laravel\ServiceProvider;
use DigitalCreative\Jaqen\Services\Scaffold\Http\Controllers\ScaffoldController;
use Illuminate\Support\Facades\Route;

class ScaffoldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::prefix('jaqen-api')->get('/scaffold/sidebar', [ ScaffoldController::class, 'sidebar' ]);
    }
}
