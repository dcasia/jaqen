<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers\Resources;

use DigitalCreative\Dashboard\Concerns\WithCustomStore;
use DigitalCreative\Dashboard\Concerns\WithEvents;
use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Http\Requests\StoreResourceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class StoreController extends Controller
{

    public function handle(StoreResourceRequest $request): JsonResponse
    {

        $resource = $request->resourceInstance();

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

        $data = $fields->pluck('value', 'attribute')->toArray();

        $fieldsWithEvents = $fields->whereInstanceOf(WithEvents::class);

        /**
         * Before Create
         */
        $fieldsWithEvents->each(function(WithEvents $field) use (&$data) {
            $data = $field->runBeforeCreate($data);
        });

        $data = $resource->runBeforeCreate($data);

        if ($resource instanceof WithCustomStore) {
            $data = $resource->storeResource($data, $request);
        } else {
            $data = $resource->repository()->create($data);
        }

        /**
         * After Create
         */
        $fieldsWithEvents->each(fn(WithEvents $field) => $field->runAfterCreate($data));

        $data = $resource->runAfterCreate($data);

        return response()->json($data, 201);

    }

}
