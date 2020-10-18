<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard;

use DigitalCreative\Dashboard\Concerns\WithCustomStore;
use DigitalCreative\Dashboard\Concerns\WithCustomUpdate;
use DigitalCreative\Dashboard\Concerns\WithEvents;
use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\PotentiallyMissing;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class FieldsCollection extends Collection
{
    public function resolveData(): array
    {
        return $this->filter(fn(PotentiallyMissing $field) => !$field->isMissing())
                    ->pluck('value', 'attribute')
                    ->toArray();
    }

    public function getFieldsWithEvents(): self
    {
        return $this->whereInstanceOf(WithEvents::class);
    }

    public function hydrate(Model $model, BaseRequest $request): self
    {
        return $this->map(fn(AbstractField $field) => $field->hydrateFromModel($model, $request));
    }

    public function getResolvedFieldsData(Model $model, BaseRequest $request): self
    {
        return $this->map(function(AbstractField $field) use ($model, $request) {
            return $field->resolveValueFromModel($model, $request)->toArray();
        });
    }

    /**
     * @param BaseRequest $request
     * @return array
     * @throws ValidationException
     */
    public function validate(BaseRequest $request): array
    {
        $rules = $this->mapWithKeys(fn(AbstractField $field) => [ $field->attribute => $field->resolveRules($request) ])
                      ->toArray();

        return $request->validate($rules);
    }

    public function update(AbstractResource $resource, Model $model, BaseRequest $request): bool
    {

        $data = $this->resolveData();

        /**
         * Events
         * Before Update
         */
        $fieldsWithEvents = $this->getFieldsWithEvents();
        $fieldsWithEvents->each(function(WithEvents $field) use ($model, &$data) {
            $data = $field->runBeforeUpdate($model, $data);
        });

        $data = $resource->runBeforeUpdate($model, $data);

        if ($resource instanceof WithCustomUpdate) {

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

    public function store(AbstractResource $resource, BaseRequest $request)
    {

        $data = $this->resolveData();

        $fieldsWithEvents = $this->getFieldsWithEvents();

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

        return $resource->runAfterCreate($data);

    }

}
