<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Traits;

trait ResolvableTrait
{
    public static function resolve(): static
    {
        return resolve(static::class);
    }
}
