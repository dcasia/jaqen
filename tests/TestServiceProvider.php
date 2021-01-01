<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests;

use DigitalCreative\Jaqen\JaqenServiceProvider;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\Article;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\ResourceWithRequiredFilters;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\User;

class TestServiceProvider extends JaqenServiceProvider
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
