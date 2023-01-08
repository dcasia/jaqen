<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager;

use DigitalCreative\Jaqen\Exceptions\FilterValidationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use JsonException;
use Throwable;

class FilterCollection extends Collection
{
    private array $rawEncodedString;

    public function __construct(Collection|array $filters = [], ?string $rawEncodedString = null)
    {
        parent::__construct($filters);

        $this->rawEncodedString = array_keys(static::decode($rawEncodedString));
    }

    public static function fake(array $data): string
    {
        try {
            return base64_encode(json_encode($data, JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            return '';
        }
    }

    public function encode(): string
    {
        return base64_encode($this->toJson());
    }

    public static function decode(?string $filters): array
    {
        if (is_null($filters)) {
            return [];
        }

        return once(function () use ($filters) {

            try {

                return json_decode(base64_decode($filters), true, 512, JSON_THROW_ON_ERROR);

            } catch (Throwable) {

                return [];

            }

        });
    }

    /**
     * @throws FilterValidationException
     */
    public function applyOnQuery(Builder $builder): Builder
    {
        return $builder->where(function (Builder $query) {

            $this
                ->filter(function (AbstractFilter $filter) {

                    if ($this->rawEncodedString) {
                        return in_array($filter::uriKey(), $this->rawEncodedString, true);
                    }

                    return false;

                })
                ->each(function (AbstractFilter $filter) use ($query) {

                    $exceptions = [];

                    try {

                        $filter->apply($query, $filter->getFieldsDataFromRequest());

                    } catch (ValidationException $exception) {

                        $exceptions[ $filter::uriKey() ] = $exception;

                    }

                    if (count($exceptions)) {

                        throw FilterValidationException::fromValidationExceptions($exceptions);

                    }

                });

        });
    }
}
