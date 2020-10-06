<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Concerns;

use Illuminate\Database\Eloquent\Model;

interface WithCrudEvent
{
    public function beforeCreate(callable $callback): self;
    public function afterCreate(callable $callback): self;

    public function beforeUpdate(callable $callback): self;
    public function afterUpdate(callable $callback): self;

    public function runBeforeCreate(array $data): array;
    public function runAfterCreate($data): void;

    public function runBeforeUpdate(Model $model, array $data): array;
    public function runAfterUpdate(Model $model): void;
}
