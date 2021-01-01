<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Concerns;

use Illuminate\Database\Eloquent\Model;

interface WithEvents
{
    public function beforeCreate(callable $callback): self;

    public function afterCreate(callable $callback): self;

    public function beforeUpdate(callable $callback): self;

    public function afterUpdate(callable $callback): self;

    public function beforeDelete(callable $callback): self;

    public function afterDelete(callable $callback): self;

    public function runBeforeCreate(array $data): array;

    public function runAfterCreate($data);

    public function runBeforeUpdate(Model $model, array $data): array;

    public function runAfterUpdate(Model $model): void;

    public function runBeforeDelete(Model $model): void;

    public function runAfterDelete(Model $model): void;
}
