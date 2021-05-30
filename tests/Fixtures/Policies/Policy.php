<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Policies;

use DigitalCreative\Jaqen\Tests\Fixtures\Models\User;
use Illuminate\Database\Eloquent\Model;

class Policy
{

    public bool $defaultValue = true;

    public function viewAny(?User $user): bool
    {
        return $this->defaultValue;
    }

    public function view(?User $user, Model $model): bool
    {
        return $this->defaultValue;
    }

    public function create(?User $user): bool
    {
        return $this->defaultValue;
    }

    public function update(?User $user, Model $model): bool
    {
        return $this->defaultValue;
    }

    public function delete(?User $user, Model $model): bool
    {
        return $this->defaultValue;
    }

    public function restore(?User $user, Model $model): bool
    {
        return $this->defaultValue;
    }

    public function forceDelete(?User $user, Model $model): bool
    {
        return $this->defaultValue;
    }

}
