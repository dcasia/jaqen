<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Http\Controllers\Relationships;

use DigitalCreative\Jaqen\Fields\Relationships\BelongsToField;
use DigitalCreative\Jaqen\Http\Requests\BelongsToResourceRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class BelongsToController extends Controller
{

    public function searchBelongsTo(BelongsToResourceRequest $request): JsonResponse
    {
        $resource = $this->resourceManager->resourceForRequest($request);

        $fieldAttribute = Str::of($request->route('field'))->before('_id')->__toString();

        $field = $resource->findFieldByAttribute($request, $fieldAttribute);

        if ($field instanceof BelongsToField && $field->isSearchable()) {

            $resource = $field->getRelatedResource();
            $repository = $resource->repository();

            $models = $repository->searchForRelatedEntries($field->resolveSearchCallback(), $request);

            $response = $models->map(function (Model $model) use ($resource, $request) {
                return [
                    'key' => $model->getKey(),
                    'fields' => $resource->resolveFieldsUsingModel($model, $request),
                ];
            });

            return response()->json($response);

        }

        abort(404);

    }

}
