<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard;

use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\FilterRequest;
use DigitalCreative\Dashboard\Traits\ResolveFieldsTrait;
use DigitalCreative\Dashboard\Traits\ResolveUriKey;
use Illuminate\Database\Eloquent\Builder;
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

    public function jsonSerialize()
    {
        return [
            'uriKey' => $this::uriKey(),
            'fields' => $this->resolveFields()
        ];
    }
}
