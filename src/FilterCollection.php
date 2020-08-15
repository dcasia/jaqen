<?php

namespace DigitalCreative\Dashboard;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use JsonException;
use Throwable;

class FilterCollection extends Collection
{
    private array $rawEncodedString;

    /**
     * FilterCollection constructor.
     *
     * @param array $filters
     * @param string|null $rawEncodedString
     */
    public function __construct($filters = [], ?string $rawEncodedString = null)
    {
        parent::__construct($filters);

        $this->rawEncodedString = array_keys(static::decode($rawEncodedString));
    }

    public static function test(array $data): string
    {
        try {
            return base64_encode(json_encode($data, JSON_THROW_ON_ERROR));
        } catch (JsonException $e) {
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

        return once(static function () use ($filters) {

            try {

                return json_decode(base64_decode($filters), true, 512, JSON_THROW_ON_ERROR);

            } catch (Throwable $exception) {

                return [];

            }

        });
    }

    public function applyOnQuery(Builder $builder)
    {
        return $builder->where(function (Builder $query) {

            $this->filter(function (AbstractFilter $filter) {

                if ($this->rawEncodedString) {
                    return in_array($filter::uriKey(), $this->rawEncodedString, true);
                }

                return false;

            })->each(fn(AbstractFilter $filter) => $filter->apply($query, $filter->getFieldsDataFromRequest()));

        });
    }

}
