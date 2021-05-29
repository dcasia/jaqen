<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Traits;

use Illuminate\Support\Str;

trait ResolveUriKey
{

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return Str::of(class_basename(static::class))->replaceMatches('~\W+~', '-')->kebab()->plural()->__toString();
    }

}
