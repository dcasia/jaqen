<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Repository;

use DigitalCreative\Dashboard\FilterCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{

    /**
     * Whatever is returned from this method is sent back to the client after the creation
     *
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data);

    public function findByKey(string $key, array $with = []): ?Model;

    public function findByKeys(array $keys): Collection;

    public function find(FilterCollection $filters, int $page, int $perPage = 15, array $with = []): Collection;

    public function update(Model $model, array $data): bool;

    public function delete(Model $model): bool;

    public function count(FilterCollection $filters): int;

}
