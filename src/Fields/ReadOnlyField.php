<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Fields;

class ReadOnlyField extends AbstractField
{
    public function isReadOnly(): bool
    {
        return true;
    }
}
