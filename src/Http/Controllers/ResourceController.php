<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Concerns\WithCustomStore;
use DigitalCreative\Dashboard\Dashboard;
use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\FieldsData;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\DeleteResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\DetailResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\IndexResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\StoreResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class ResourceController extends Controller
{

    /**
     * Return a list of all registered resources
     *
     * @param BaseRequest $request
     * @return Collection
     */
    public function list(BaseRequest $request): Collection
    {
        return Dashboard::getInstance()
                        ->allAuthorizedResources($request)
                        ->map(function(AbstractResource $resource) {
                            return $resource->getDescriptor();
                        });
    }

    public function searchBelongsTo(BaseRequest $request): Collection
    {
        return $request->resourceInstance()->searchBelongsToRelation();
    }

    public function filters(IndexResourceRequest $request): array
    {
        return $request->resourceInstance()->getFiltersListing();
    }

    public function index(IndexResourceRequest $request): array
    {
        return $request->resourceInstance()->index();
    }

    public function update(UpdateResourceRequest $request): bool
    {
        return $request->resourceInstance()->update();
    }

    public function delete(DeleteResourceRequest $request): bool
    {
        return $request->resourceInstance()->delete();
    }

    public function store(StoreResourceRequest $request): void
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

            $resource->storeResource($request, $data);

        } else {

            $resource->repository()->create($data);

        }

        $callbacks->filter()->each(fn(callable $function) => $function());

    }

    public function fields(StoreResourceRequest $request): Collection
    {
        return $request->resourceInstance()->resolveFields($request);
    }

    public function fetch(DetailResourceRequest $request): array
    {
        return $request->resourceInstance()->detail();
    }

}
