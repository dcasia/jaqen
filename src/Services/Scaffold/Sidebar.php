<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Scaffold;

use DigitalCreative\Jaqen\Traits\MakeableTrait;
use JsonSerializable;

class Sidebar implements JsonSerializable
{
    use MakeableTrait;

    private array $entries;

    public function __construct(array $entries)
    {
        $this->entries = $entries;
    }

    public function jsonSerialize(): array
    {
        return $this->entries;
    }
}
