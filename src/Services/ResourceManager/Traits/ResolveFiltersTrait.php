<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Traits;

use DigitalCreative\Jaqen\Services\ResourceManager\AbstractFilter;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use Illuminate\Support\Collection;

/**
 * @property BaseRequest $request
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
