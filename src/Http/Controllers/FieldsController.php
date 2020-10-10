<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Http\Requests\FieldsResourceRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class FieldsController extends Controller
{
    /**
     * Return a list of all available fields for a given resource
     *
     * @param FieldsResourceRequest $request
     * @return Collection
     */
    public function fields(FieldsResourceRequest $request): Collection
    {
        return $request->resourceInstance()->resolveFields($request);
    }
}
