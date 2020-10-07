<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers\Relationships;

use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Http\Requests\BelongsToResourceRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BelongsToController extends Controller
{

    public function searchBelongsTo(BelongsToResourceRequest $request): JsonResponse
    {
        $resource = $request->resourceInstance();

        $field = $resource->findFieldByAttribute($request, $request->route('field'));

        if ($field instanceof BelongsToField && $field->isSearchable()) {

            $resource = $field->getRelatedResource();
            $repository = $resource->repository();

            $models = $repository->searchForRelatedEntries(
                $field->resolveSearchCallback(), $request
            );

            $response = $models->map(function(Model $model) use ($resource, $request) {
                return collect($resource->resolveFieldsUsingModel($model, $request)->jsonSerialize())->pluck('value', 'attribute');
            });

            return response()->json($response);

        }

        abort(404);

    }

}
