<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Concerns;

use DigitalCreative\Dashboard\FieldsData;
use DigitalCreative\Dashboard\Http\Requests\StoreResourceRequest;

interface WithCustomStore
{
    public function storeResource(StoreResourceRequest $request, FieldsData $data): void;
}
