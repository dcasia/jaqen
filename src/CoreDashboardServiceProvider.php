<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard;

use Illuminate\Support\ServiceProvider;

class CoreDashboardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

    }

    public function registerPublishing(): void
    {

        $this->publishes([
            __DIR__ . '/Console/DashboardServiceProvide.stub.php' => app_path('Providers/DashboardServiceProvider.php'),
        ], 'dashboard-provider');

    }

}
