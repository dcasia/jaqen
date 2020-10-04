<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Repository;

use DigitalCreative\Dashboard\FieldsData;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{

    public function findByKey(string $key): ?Model;

    /**
     * Whatever is returned from this method is sent back to the client after the creation
     *
     * @param FieldsData $data
     *
     * @return mixed
     */
    public function create(FieldsData $data);

}
