<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Traits;

use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
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
        if ($request->isCreate()) {
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
     *
     * @param array $data
     * @return $this
     */
    public function hydrateFromArray(array $data): self
    {
        $this->value = data_get($data, $this->attribute);

        return $this;
    }

}
