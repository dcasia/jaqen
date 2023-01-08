<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen;

use DigitalCreative\Jaqen\Services\Fields\FieldsServiceProvider;
use DigitalCreative\Jaqen\Services\ResourceManager\ResourceManagerServiceProvider;
use DigitalCreative\Jaqen\Services\Scaffold\ScaffoldServiceProvider;
use Illuminate\Support\ServiceProvider;

abstract class JaqenServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $services = [
            FieldsServiceProvider::class,
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
