<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers;

use DigitalCreative\Jaqen\Services\Fields\AbstractField;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\StoreResourceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class StoreController extends Controller
{

    public function handle(StoreResourceRequest $request): JsonResponse
    {

        $resource = $this->resourceManager->resourceForRequest($request);

        /**
         * Validate all fields and throw validation exception in case of invalid data
         *
         * @var $fields Collection
         * @var $validatedData array
         */
        [ $fields, $validatedData ] = $resource->resolveValidatedFields($request);

        /**
         * Remove all non updatable fields (readonly)
         * Call fill on all fields to populate the FieldsData object with it's final value
         * Return an array of functions to be called after the model has been persisted to the database
         */
        $fields = $resource->filterNonUpdatableFields($fields)
                           ->map(fn(AbstractField $field) => $field->resolveValueFromRequest($request));

        return response()->json($fields->store($resource, $request), 201);

    }

}
