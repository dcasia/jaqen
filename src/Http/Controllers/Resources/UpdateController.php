<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers\Resources;

use DigitalCreative\Dashboard\Concerns\WithCustomUpdate;
use DigitalCreative\Dashboard\Concerns\WithEvents;
use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class UpdateController extends Controller
{

    public function handle(UpdateResourceRequest $request): bool
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
                return $field->hydrateFromArray($updateData, $request)->resolveValueFromRequest($request);
            })
            ->filter(function(AbstractField $field) use ($request) {
                return $field->isRequired($request) || $field->isDirty();
            });

        $data = $fields->pluck('value', 'attribute')->toArray();

        /**
         * Events
         * Before Update
         */
        $fieldsWithEvents = $fields->whereInstanceOf(WithEvents::class);
        $fieldsWithEvents->each(function(WithEvents $field) use ($model, &$data) {
            $data = $field->runBeforeUpdate($model, $data);
        });

        $data = $resource->runBeforeUpdate($model, $data);

        if ($resource instanceof WithCustomUpdate) {

            /**
             * @todo find a better method name for WithCustomUpdate methods
             */
            $response = $resource->updateResource($model, $data, $request);

        } else {

            $response = $resource->repository()->update($model, $data);

        }

        /**
         * After Update
         */
        $fieldsWithEvents->each(fn(WithEvents $field) => $field->runAfterUpdate($model));
        $resource->runAfterUpdate($model);

        return $response;

    }

}
