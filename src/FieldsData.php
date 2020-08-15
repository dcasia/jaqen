<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard;

use Illuminate\Support\Fluent;

class FieldsData extends Fluent
{
    public function setAttribute(string $attribute, $value): self
    {
        $this->offsetSet($attribute, $value);

        return $this;
    }
}
