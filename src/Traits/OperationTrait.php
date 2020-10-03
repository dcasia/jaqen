<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Traits;

use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Repository\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait OperationTrait
{

    public function delete(): bool
    {
        return $this->repository()->deleteResource($this->findResource());
    }

    public function searchBelongsToRelation(): Collection
    {

        $request = $this->getRequest();

        $field = $this->findFieldByAttribute($request->route('field'));

        if ($field instanceof BelongsToField && $field->isSearchable()) {

            $resource = $field->getRelatedResource();
            $repository = new Repository($resource->getModel());

            $models = $repository->searchForRelatedEntries(
                $field->resolveSearchCallback(), $request
            );

            return $models->map(static function(Model $model) use ($resource, $request) {
                return collect($resource->resolveFieldsUsingModel($model, $request)->jsonSerialize())->pluck('value', 'attribute');
            });

        }

        return abort(404);

    }

    private function findResource(): ?Model
    {
        return once(function() {
            return $this->repository()->findByKey(
                $this->request->route('key')
            );
        });
    }

}
