<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Repository;

use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function findByKey(string $key): ?Model;
}
