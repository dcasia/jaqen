<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers;

use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\DetailResourceRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Throwable;

class DetailController extends Controller
{

    /**
     * @throws AuthorizationException|Throwable
     */
    public function handle(DetailResourceRequest $request): JsonResponse
    {
        $resource = $this->resourceManager->resourceForRequest($request);
        $resource->authorizeTo('view');

        $model = $resource->repository()->findByKey($request->route('key'), $resource->with);

        return response()->json([
            'key' => $model->getKey(),
            'fields' => $resource->resolveFieldsUsingModel($model, $request),
        ]);
    }

}
