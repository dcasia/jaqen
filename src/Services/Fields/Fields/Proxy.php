<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Fields\Fields;

use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use Illuminate\Database\Eloquent\Model;

abstract class Proxy extends AbstractField
{

    protected abstract function getFields(): FieldsCollection;

    public function resolveValueFromModel(Model $model, BaseRequest $request): self
    {
        $this->getFields()->each(fn(AbstractField $field) => $field->resolveValueFromModel($model, $request));

        return $this;
    }

    public function resolveValueFromRequest(BaseRequest $request): self
    {
        $this->getFields()->each(fn(AbstractField $field) => $field->resolveValueFromRequest($request));

        return $this;
    }

    public function resolveValueFromArray(array $data, BaseRequest $request): self
    {
        $this->getFields()->each(fn(AbstractField $field) => $field->resolveValueFromArray($data, $request));

        return $this;
    }

    public function resolveValueFromDefaults(BaseRequest $request): self
    {
        $this->getFields()->each(fn(AbstractField $field) => $field->resolveValueFromDefaults($request));

        return $this;
    }

    public function boot($resource, BaseRequest $request): void
    {
        $this->getFields()->each(fn(AbstractField $field) => $field->boot($resource, $request));
    }

}
