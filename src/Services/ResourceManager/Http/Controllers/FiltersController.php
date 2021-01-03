<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers;

use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\IndexResourceRequest;
use Illuminate\Http\JsonResponse;

class FiltersController extends Controller
{
    public function filters(IndexResourceRequest $request): JsonResponse
    {
        return response()->json(
            $this->resourceManager->resourceForRequest($request)->resolveFilters()
        );
    }
}
