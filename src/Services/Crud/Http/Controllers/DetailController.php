<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Crud\Http\Controllers;

use DigitalCreative\Jaqen\Services\Crud\Http\Requests\DetailResourceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class DetailController extends Controller
{

    public function handle(DetailResourceRequest $request): JsonResponse
    {
        $resource = $request->resourceInstance();

        $model = $resource->repository()->findByKey($request->route('key'), $resource->with);

        return response()->json([
            'key' => $model->getKey(),
            'fields' => $resource->resolveFieldsUsingModel($model, $request),
        ]);
    }

}
