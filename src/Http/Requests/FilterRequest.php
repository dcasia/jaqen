<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Http\Requests;

use DigitalCreative\Jaqen\Services\ResourceManager\FilterCollection;

class FilterRequest extends BaseRequest
{
    public static function createFromFilter(BaseRequest $from, string $uriKey): self
    {
        $decoded = FilterCollection::decode($from->query('filters'));

        return new self($decoded[ $uriKey ] ?? []);
    }
}
