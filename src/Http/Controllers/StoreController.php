<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Concerns\WithCustomStore;
use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\FieldsData;
use DigitalCreative\Dashboard\Http\Requests\StoreResourceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class StoreController extends Controller
{

    public function store(StoreResourceRequest $request): JsonResponse
    {

        $resource = $request->resourceInstance();

        /**
         * Validate all fields and throw validation exception in case of invalid data
         *
         * @var $fields Collection
         * @var $validatedData array
         */
        [ $fields, $validatedData ] = $resource->resolveValidatedFields($request);

        $data = new FieldsData();

        /**
         * Remove all non updatable fields (readonly)
         * Call fill on all fields to populate the FieldsData object with it's final value
         * Return an array of functions to be called after the model has been persisted to the database
         */
        $callbacks = $resource->filterNonUpdatableFields($fields)
                              ->map(function(AbstractField $field) use ($data, $validatedData, $request) {
                                  return $field->fill($data, $validatedData, $request);
                              });

        if ($resource instanceof WithCustomStore) {

            $data = $resource->storeResource($request, $data);

        } else {

            $data = $resource->repository()->create($data);

        }

        $callbacks->filter()->each(fn(callable $function) => $function());

        return response()->json($data, 201);

    }

}
