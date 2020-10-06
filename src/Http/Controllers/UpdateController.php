<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Concerns\WithFieldEvent;
use DigitalCreative\Dashboard\Concerns\WithCustomUpdate;
use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class UpdateController extends Controller
{

    public function update(UpdateResourceRequest $request): bool
    {

        $resource = $request->resourceInstance();

        /**
         * Validate all fields and throw validation exception in case of invalid data
         *
         * @var $fields Collection
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
            ->map(function(AbstractField $field) use ($updateData, $request) {
                return $field->hydrateFromArray($updateData)->resolveValueFromRequest($request);
            })
            ->filter(function(AbstractField $field) use ($request) {
                return $field->isRequired($request) || $field->isDirty();
            });

        $data = $fields->pluck('value', 'attribute')->toArray();

        /**
         * Events
         */
        $fieldsWithEvents = $fields->whereInstanceOf(WithFieldEvent::class);

        /**
         * Before Update
         */
        $fieldsWithEvents->each(function(WithFieldEvent $field) use ($model, &$data) {
            $data = $field->runBeforeUpdate($model, $data);
        });

        if ($resource instanceof WithCustomUpdate) {

            $response = $resource->updateResource($model, $data, $request);

        } else {

            $response = $resource->repository()->update($model, $data);

        }

        /**
         * After Update
         */
        $fieldsWithEvents->each(fn(WithFieldEvent $field) => $field->runAfterUpdate($model));

        return $response;

    }

}
