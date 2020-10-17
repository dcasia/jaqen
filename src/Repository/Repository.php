<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Repository;

use DigitalCreative\Dashboard\FilterCollection;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Repository implements RepositoryInterface
{
    /**
     * @var Model
     */
    private Model $model;

    /**
     * ResourceRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function searchForRelatedEntries(callable $userDefinedCallback, BaseRequest $request): Collection
    {
        return $userDefinedCallback($this->newQuery(), $request)->get();
    }

    public function getOptionsForRelatedResource(callable $userDefinedCallback, BaseRequest $request): Collection
    {
        return $this->searchForRelatedEntries($userDefinedCallback, $request);
    }

    public function create(array $data): Model
    {
        return tap($this->newModel()->forceFill($data))->save();
    }

    public function saveMany(BelongsToMany $relation, array $models, array $pivotAttributes = []): void
    {
        $relation->saveMany($models, $pivotAttributes);
    }

    public function updatePivot(BelongsToMany $relation, string $key, array $pivotAttributes = []): void
    {
        $relation->updateExistingPivot($key, $pivotAttributes);
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function count(FilterCollection $filters): int
    {
        return $this->applyFilterToQuery($filters)->count();
    }

    public function find(FilterCollection $filters, int $page, int $perPage = 15, array $with = []): Collection
    {
        return $this->applyFilterToQuery($filters)->with($with)->forPage($page, $perPage)->get();
    }

    public function findByKey(string $key, array $with = []): ?Model
    {
        return $this->newQuery()->whereKey($key)->with($with)->firstOrFail();
    }

    public function findByKeys(array $keys): Collection
    {
        return $this->newQuery()->findMany($keys);
    }

    public function update(Model $model, array $data): bool
    {
        $model->forceFill($data);

        if ($model->isDirty()) {

            return $model->save();

        }

        return true;
    }

    private function applyFilterToQuery(FilterCollection $filters): Builder
    {
        return $filters->applyOnQuery($this->newQuery());
    }

    private function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    private function newModel(): Model
    {
        return $this->model->newInstance();
    }

}
