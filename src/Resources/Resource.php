<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Resources;

use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Repository\Repository;
use DigitalCreative\Dashboard\Repository\RepositoryInterface;
use DigitalCreative\Dashboard\Traits\MakeableTrait;
use DigitalCreative\Dashboard\Traits\OperationTrait;
use DigitalCreative\Dashboard\Traits\ResolveFieldsTrait;
use DigitalCreative\Dashboard\Traits\ResolveFiltersTrait;
use DigitalCreative\Dashboard\Traits\ResolveUriKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class Resource
{

    use ResolveFieldsTrait;
    use ResolveFiltersTrait;
    use ResolveUriKey;
    use MakeableTrait;
    use OperationTrait;

    private BaseRequest $request;

    public function __construct(BaseRequest $request)
    {
        $this->request = $request;
    }

    abstract public function getModel(): Model;

    public function getDescriptor(): array
    {
        return [
            'name' => $this->label(),
            'label' => Str::plural($this->label()),
            'uriKey' => static::uriKey(),
        ];
    }

    public static function humanize(string $value): string
    {
        return Str::title(Str::snake($value, ' '));
    }

    public function label(): string
    {
        return static::humanize(class_basename(static::class));
    }

    public function repository(): RepositoryInterface
    {
        return new Repository($this->getModel());
    }

    public function getFiltersListing(): array
    {
        return $this->resolveFilters()->toArray();
    }

}
