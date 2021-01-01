<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Http\Controllers;

use DigitalCreative\Jaqen\Http\Requests\IndexResourceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class FiltersController extends Controller
{
    public function filters(IndexResourceRequest $request): JsonResponse
    {
        return response()->json($request->resourceInstance()->resolveFilters());
    }
}
