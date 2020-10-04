<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Concerns;

use DigitalCreative\Dashboard\FieldsData;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use Illuminate\Database\Eloquent\Model;

interface WithCustomUpdate
{
    public function updateResource(Model $model, FieldsData $data, UpdateResourceRequest $request): bool;
}
