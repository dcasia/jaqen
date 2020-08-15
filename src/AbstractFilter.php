<?php

namespace DigitalCreative\Dashboard;

use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\FilterRequest;
use DigitalCreative\Dashboard\Traits\ResolveFieldsTrait;
use DigitalCreative\Dashboard\Traits\ResolveUriKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use JsonSerializable;

class AbstractFilter implements JsonSerializable
{

    use ResolveFieldsTrait;
    use ResolveUriKey;

    public function apply(Builder $builder, FieldsData $value): Builder
    {
        return $builder;
    }

    protected function getRequest(): BaseRequest
    {
        return FilterRequest::createFromFilter(
            app(BaseRequest::class), $this::uriKey()
        );
    }

    public function toValues(): Collection
    {
        return $this->resolveFields()->mapWithKeys(function (AbstractField $field) {
            return [
                $field->attribute => $field->value
            ];
        });
    }

    public function jsonSerialize()
    {
        return [
            'uriKey' => $this::uriKey(),
            'fields' => $this->resolveFields()
        ];
    }
}
