<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Traits;

trait MakeableTrait
{
    /**
     * @param mixed ...$arguments
     *
     * @return static
     */
    public static function make(...$arguments): self
    {
        return new static(...$arguments);
    }
}
