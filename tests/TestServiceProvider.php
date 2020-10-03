<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests;

use DigitalCreative\Dashboard\DashboardServiceProvider;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\Article;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\ResourceWithRequiredFilters;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\User;

class TestServiceProvider extends DashboardServiceProvider
{
    public function resources(): array
    {
        return [
            User::class,
            Article::class,
            ResourceWithRequiredFilters::class,
        ];
    }
}
