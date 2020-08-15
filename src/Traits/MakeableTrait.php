<?php

namespace DigitalCreative\Dashboard\Traits;

trait MakeableTrait
{
    /**
     * @param mixed ...$arguments
     *
     * @return static
     */
    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }
}
