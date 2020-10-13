<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Fields;

use DigitalCreative\Dashboard\AbstractFilter;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Traits\MakeableTrait;
use DigitalCreative\Dashboard\Traits\ResolveRulesTrait;
use DigitalCreative\Dashboard\Traits\ResolveValueTrait;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class AbstractField implements JsonSerializable, Arrayable
{

    use ResolveRulesTrait;
    use MakeableTrait;
    use ResolveValueTrait;

    public string $label;
    public string $attribute;
    public array $additionalInformation = [];
    protected AbstractResource $parentResource;

    /**
     * @var callable|mixed
     */
    private $readOnly = false;

    public function __construct(string $label, string $attribute = null)
    {
        $this->label = $label;
        $this->attribute = $attribute ?? $this->generateAttribute($label);
    }

    public function boot($resource, BaseRequest $request): void
    {
        if ($resource instanceof AbstractResource) {
            $this->setParentResource($resource);
        } else if ($resource instanceof AbstractFilter) {
            //
        }
    }

    public function setParentResource(AbstractResource $resource): self
    {
        $this->parentResource = $resource;

        return $this;
    }

    /**
     * @param bool|callable $state
     *
     * @return $this
     */
    public function readOnly($state = true): self
    {
        $this->readOnly = $state;

        return $this;
    }

    public function isReadOnly(): bool
    {
        return (bool) value($this->readOnly);
    }

    public function isRequired(BaseRequest $request): bool
    {
        return in_array('required', $this->resolveRules($request), true);
    }

    protected function resolveAdditionalInformation(): ?array
    {
        $response = collect($this->additionalInformation)->flatMap(fn($value) => value($value));

        if ($response->isEmpty()) {
            return null;
        }

        return $response->toArray();
    }

    /**
     * @param array|callable $options
     *
     * @return $this
     */
    public function withAdditionalInformation($options): self
    {
        $this->additionalInformation[] = $options;

        return $this;
    }

    public function component(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    private function generateAttribute(string $label): string
    {
        return Str::of($label)->trim()->replaceMatches('~\s+~', '_')->lower()->snake()->__toString();
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    public function jsonSerialize(): array
    {
        return [
            'label' => $this->label,
            'attribute' => $this->attribute,
            'value' => $this->value,
            'component' => $this->component(),
            'additionalInformation' => $this->resolveAdditionalInformation(),
        ];
    }

}
