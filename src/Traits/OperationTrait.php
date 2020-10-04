<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Traits;

use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Repository\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait OperationTrait
{

    public function searchBelongsToRelation(BaseRequest $request): Collection
    {

        $field = $this->findFieldByAttribute($request, $request->route('field'));

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

}
