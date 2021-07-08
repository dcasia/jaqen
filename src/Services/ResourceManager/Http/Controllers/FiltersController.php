<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers;

use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\IndexResourceRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Throwable;

class FiltersController extends Controller
{

    /**
     * @throws AuthorizationException|Throwable
     */
    public function filters(IndexResourceRequest $request): JsonResponse
    {
        $resource = $this->resourceManager->resourceForRequest($request);
        $resource->authorizeTo('viewAny');

        return response()->json($resource->resolveFilters());
    }

}
