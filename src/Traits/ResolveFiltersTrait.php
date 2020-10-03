<?php

declare(strict_types = 1);

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

    public function resolveFilters(): Collection
    {
        return once(function () {
            return collect($this->filters())->merge($this->filters);
        });
    }

    public function addFilters(AbstractFilter ...$filters): self
    {
        $this->filters = array_merge($this->filters, $filters);

        return $this;
    }

}
