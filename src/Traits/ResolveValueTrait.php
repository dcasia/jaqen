<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Traits;

use DigitalCreative\Jaqen\Services\Fields\AbstractField;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use Illuminate\Database\Eloquent\Model;

trait ResolveValueTrait
{

    /**
     * @var string|int|null
     */
    public $value;

    /**
     * @var callable|mixed
     */
    private $defaultCallback;

    public bool $dirty = false;

    public function isDirty(): bool
    {
        return $this->dirty;
    }

    /**
     * @param string|int|callable $value
     *
     * @return AbstractField
     */
    public function default($value): self
    {
        $this->defaultCallback = $value;

        return $this;
    }

    public function setValue($value, BaseRequest $request): self
    {
        if ($request->isSchemaFetching()) {
            $value = value($this->defaultCallback);
        }

        $this->dirty = $this->value !== $value;
        $this->value = $value;

        return $this;
    }

    public function resolveValueFromModel(Model $model, BaseRequest $request): self
    {
        return $this->resolveValueFromArray($model->toArray(), $request);
    }

    public function resolveValueFromRequest(BaseRequest $request): self
    {
        return $this->resolveValueFromArray($request->toArray(), $request);
    }

    public function resolveValueFromArray(array $data, BaseRequest $request): self
    {
        return $this->setValue(data_get($data, $this->attribute), $request);
    }

    /**
     * The value set from this method is intended to represent
     * the real value that is persisted already on a database
     * therefore it should never be considered as "dirty"
     *
     * @param array $data
     * @param BaseRequest $request
     *
     * @return $this
     */
    public function hydrateFromArray(array $data, BaseRequest $request): self
    {
        $this->setValue(data_get($data, $this->attribute), $request);
        $this->dirty = false;

        return $this;
    }

    public function hydrateFromModel(Model $model, BaseRequest $request): self
    {
        return $this->hydrateFromArray($model->toArray(), $request);
    }

}
