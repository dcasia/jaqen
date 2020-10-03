<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Traits;

use Illuminate\Support\Str;

trait ResolveUriKey
{

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey(): string
    {
        return Str::of(class_basename(static::class))->slug()->kebab()->plural()->__toString();
    }

}
