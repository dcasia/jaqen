<?php

namespace DigitalCreative\Dashboard\Tests;

use DigitalCreative\Dashboard\DashboardServiceProvider;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\Client;
use JohnDoe\BlogPackage\BlogPackageServiceProvider;

class TestServiceProvider extends DashboardServiceProvider
{
    public function resources(): array
    {
        return [
            Client::class
        ];
    }
}
