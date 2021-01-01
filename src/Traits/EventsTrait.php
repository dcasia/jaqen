<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Traits;

use Illuminate\Database\Eloquent\Model;

trait EventsTrait
{

    private array $beforeCreateCallbacks = [];
    private array $afterCreateCallbacks = [];

    private array $beforeUpdateCallbacks = [];
    private array $afterUpdateCallbacks = [];

    private array $beforeDeleteCallbacks = [];
    private array $afterDeleteCallbacks = [];

    public function beforeCreate(callable $callback): self
    {
        $this->beforeCreateCallbacks[] = $callback;

        return $this;
    }

    public function afterCreate(callable $callback): self
    {
        $this->afterCreateCallbacks[] = $callback;

        return $this;
    }

    public function beforeUpdate(callable $callback): self
    {
        $this->beforeUpdateCallbacks[] = $callback;

        return $this;
    }

    public function afterUpdate(callable $callback): self
    {
        $this->afterUpdateCallbacks[] = $callback;

        return $this;
    }

    public function beforeDelete(callable $callback): self
    {
        $this->beforeDeleteCallbacks[] = $callback;

        return $this;
    }

    public function afterDelete(callable $callback): self
    {
        $this->afterDeleteCallbacks[] = $callback;

        return $this;
    }

    public function runBeforeCreate(array $data): array
    {

        foreach ($this->beforeCreateCallbacks as $callback) {

            if (($result = $callback($data)) && is_array($result)) {

                $data = $result;

            }

        }

        return $data;

    }

    public function runAfterCreate($data)
    {
        foreach ($this->afterCreateCallbacks as $callback) {
            $data = $callback($data);
        }

        return $data;
    }

    public function runBeforeUpdate(Model $model, array $data): array
    {

        foreach ($this->beforeUpdateCallbacks as $callback) {

            if (($result = $callback($model, $data)) && is_array($result)) {

                $data = $result;

            }

        }

        return $data;

    }

    public function runAfterUpdate(Model $model): void
    {
        foreach ($this->afterUpdateCallbacks as $callback) {
            $callback($model);
        }
    }

    public function runBeforeDelete(Model $model): void
    {
        foreach ($this->beforeDeleteCallbacks as $callback) {
            $callback($model);
        }
    }

    public function runAfterDelete(Model $model): void
    {
        foreach ($this->afterDeleteCallbacks as $callback) {
            $callback($model);
        }
    }

}
