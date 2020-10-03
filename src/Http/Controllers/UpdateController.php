<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

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
         * Resolve all fields using the validated data
         * Remove fields that wasn't modified, and ensure required fields is always present
         */
        $fields = $fields->map(fn(AbstractField $field) => $field->resolveUsingData($validatedData))
                         ->filter(function(AbstractField $field) use ($request) {
                             return $field->isRequired($request) || $field->isDirty();
                         });

        $model = $resource->repository()->findByKey($request->route('key'));

        $fieldsData = $fields->pluck('value', 'attribute')->toArray();

        return $resource->repository()->updateResource($model, $fieldsData);

    }

}
