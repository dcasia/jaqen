<?php

namespace DigitalCreative\Dashboard\Traits;


use DigitalCreative\Dashboard\AbstractFilter;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use Illuminate\Support\Collection;

/**
 * Trait ResolveFiltersTrait
 *
 * @property BaseRequest $request
 *
 * @package DigitalCreative\Dashboard\Traits
 */
trait ResolveFiltersTrait
{

    private array $filters = [];

    public function filters(): array
    {
        return [];
    }

    private function resolveFilters(): Collection
    {
        return once(function () {
            return collect($this->filters())->merge($this->filters);
        });
    }

    public function addFilter(AbstractFilter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

}
