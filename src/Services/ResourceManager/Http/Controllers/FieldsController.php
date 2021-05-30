<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers;

use DigitalCreative\Jaqen\Http\Requests\FieldsResourceRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Throwable;

class FieldsController extends Controller
{

    /**
     * Return a list of all available fields for a given resource
     *
     * @throws AuthorizationException|Throwable
     */
    public function fields(FieldsResourceRequest $request): JsonResponse
    {
        $resource = $this->resourceManager->resourceForRequest($request);
        $resource->authorizeTo('viewAny');

        return response()->json($resource->resolveFields($request));
    }

}
