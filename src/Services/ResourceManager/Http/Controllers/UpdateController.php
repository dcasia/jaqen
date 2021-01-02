<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers;

use DigitalCreative\Jaqen\Fields\AbstractField;
use DigitalCreative\Jaqen\FieldsCollection;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\UpdateResourceRequest;
use Illuminate\Http\JsonResponse;

class UpdateController extends Controller
{

    public function handle(UpdateResourceRequest $request): JsonResponse
    {

        $resource = $this->resourceManager->resourceForRequest($request);

        /**
         * Validate all fields and throw validation exception in case of invalid data
         *
         * @var $fields FieldsCollection
         * @var $validatedData array
         */
        [ $fields, $validatedData ] = $resource->resolveNonUpdatableValidatedFields($request);

        /**
         * Remove all non updatable fields (readonly)
         * Remove fields that wasn't modified, and ensure required fields is always present
         */
        $model = $resource->repository()->findByKey($request->route('key'));

        $updateData = $model->only(array_keys($validatedData));

        $fields = $fields
            ->map(function (AbstractField $field) use ($updateData, $request) {
                return $field->hydrateFromArray($updateData, $request)->resolveValueFromRequest($request);
            })
            ->filter(function (AbstractField $field) use ($request) {
                return $field->isRequired($request) || $field->isDirty();
            });

        return response()->json($fields->update($resource, $model, $request), 200);

    }

}
