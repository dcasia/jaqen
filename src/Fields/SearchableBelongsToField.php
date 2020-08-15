<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Fields;

use Closure;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use Illuminate\Database\Eloquent\Builder;

class SearchableBelongsToField extends BelongsToField
{

    public function __construct(string $label, string $relation, string $resource)
    {
        parent::__construct($label, $relation, $resource);
    }

    private ?Closure $onSearchCallback = null;

    public function resolveSearchCallback(): callable
    {
        return $this->onSearchCallback ?? static function (Builder $builder, BaseRequest $request): Builder {
                return $builder->when($request->query('id'), fn(Builder $builder, string $search) => $builder->whereKey($search))
                               ->limit(10);
            };
    }

    public function onSearch(callable $callback): self
    {
        $this->onSearchCallback = $callback;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [ 'value' => $this->resolveValue(), ]);
    }

}
