<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Concerns;

use DigitalCreative\Jaqen\Http\Requests\UpdateResourceRequest;
use Illuminate\Database\Eloquent\Model;

interface WithCustomUpdate
{
    public function updateResource(Model $model, array $data, UpdateResourceRequest $request): bool;
}
