<?php

namespace DigitalCreative\Dashboard\Http\Requests;

use DigitalCreative\Dashboard\FilterCollection;

class FilterRequest extends BaseRequest
{


    public static function createFromFilter(BaseRequest $from, string $uriKey): self
    {

        $decoded = FilterCollection::decode($from->query('filters'));

        return new self($decoded[ $uriKey ] ?? []);

    }

}
