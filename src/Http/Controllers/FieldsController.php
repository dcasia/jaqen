<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Http\Controllers;

use DigitalCreative\Jaqen\Http\Requests\FieldsResourceRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class FieldsController extends Controller
{
    /**
     * Return a list of all available fields for a given resource
     *
     * @param FieldsResourceRequest $request
     *
     * @return JsonResponse
     */
    public function fields(FieldsResourceRequest $request): JsonResponse
    {
        return response()->json(
            $this->resourceManager->resourceForRequest($request)->resolveFields($request)
        );
    }
}
