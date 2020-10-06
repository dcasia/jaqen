<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Repository;

use DigitalCreative\Dashboard\FieldsData;
use DigitalCreative\Dashboard\FilterCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{

    /**
     * Whatever is returned from this method is sent back to the client after the creation
     *
     * @param FieldsData $data
     *
     * @return mixed
     */
    public function create(FieldsData $data);

    public function findByKey(string $key): ?Model;

    public function find(FilterCollection $filters, int $page, int $perPage = 15): Collection;

    public function update(Model $model, array $data): bool;

    public function delete(array $ids): bool;

    public function count(FilterCollection $filters): int;

}
