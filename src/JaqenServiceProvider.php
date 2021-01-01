<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class JaqenServiceProvider extends ServiceProvider
{

    abstract public function resources(): array;

    public function boot(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });

        $this->app->singleton(Jaqen::class, function () {
            return (new Jaqen())->setResources($this->resources());
        });

    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => 'jaqen-api',
        ];
    }

}
