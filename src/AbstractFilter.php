<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen;

use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Http\Requests\FilterRequest;
use DigitalCreative\Jaqen\Traits\ResolveFieldsTrait;
use DigitalCreative\Jaqen\Traits\ResolveUriKey;
use Illuminate\Database\Eloquent\Builder;
use JsonSerializable;

abstract class AbstractFilter implements JsonSerializable
{

    use ResolveFieldsTrait;
    use ResolveUriKey;

    abstract public function apply(Builder $builder, FieldsData $value): Builder;

    /**
     * @return BaseRequest
     * @todo delete
     */
    protected function getRequest(): BaseRequest
    {
        return FilterRequest::createFromFilter(
            app(BaseRequest::class), $this::uriKey()
        );
    }

    public function jsonSerialize()
    {
        return [
            'uriKey' => static::uriKey(),
            'fields' => $this->resolveFields(app(BaseRequest::class)),
        ];
    }

}
