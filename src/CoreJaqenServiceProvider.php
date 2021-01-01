<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen;

use Illuminate\Support\ServiceProvider;

class CoreJaqenServiceProvider extends ServiceProvider
{

    public function boot(): void
    {

        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

    }

    public function registerPublishing(): void
    {

        $this->publishes([
            __DIR__ . '/Console/stubs/JaqenServiceProvider.stub' => app_path('Providers/JaqenServiceProvider.php'),
        ], 'jaqen-provider');

    }

}
