<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Http\Controllers;

use DigitalCreative\Jaqen\Services\ResourceManager\ResourceManager;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\IndexResourceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class FiltersController extends Controller
{
    public function filters(IndexResourceRequest $request, ResourceManager $crud): JsonResponse
    {
        return response()->json($crud->resourceForRequest($request)->resolveFilters());
    }
}
