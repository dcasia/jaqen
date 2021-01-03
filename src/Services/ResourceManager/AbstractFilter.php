<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager;

use DigitalCreative\Jaqen\Services\Fields\FieldsData;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Http\Requests\FilterRequest;
use DigitalCreative\Jaqen\Traits\MakeableTrait;
use DigitalCreative\Jaqen\Services\Fields\Traits\ResolveFieldsTrait;
use DigitalCreative\Jaqen\Traits\ResolveUriKey;
use Illuminate\Database\Eloquent\Builder;
use JsonSerializable;

abstract class AbstractFilter implements JsonSerializable
{

    use ResolveFieldsTrait;
    use ResolveUriKey;
    use MakeableTrait;

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

    public function jsonSerialize(): array
    {
        return [
            'uriKey' => static::uriKey(),
            'fields' => $this->resolveFields($this->getRequest()),
        ];
    }

}
