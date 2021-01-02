<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Traits;

trait MakeableTrait
{
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
}
