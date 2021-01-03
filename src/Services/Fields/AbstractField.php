<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Fields;

use DigitalCreative\Jaqen\AbstractFilter;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Traits\MakeableTrait;
use DigitalCreative\Jaqen\Traits\ResolveRulesTrait;
use DigitalCreative\Jaqen\Traits\ResolveValueTrait;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\PotentiallyMissing;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class AbstractField implements JsonSerializable, Arrayable, PotentiallyMissing
{

    use ResolveRulesTrait;
    use MakeableTrait;
    use ResolveValueTrait;

    public string $label;
    public string $attribute;
    public array $additionalInformation = [];
    public array $data = [];

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

    public function isMissing(): bool
    {
        return false;
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
        $rules = $this->resolveRules($request);

        /**
         * @todo handle conditional values https://laravel.com/docs/8.x/validation#conditionally-adding-rules
         */
        if ((in_array('sometimes', $rules, true) && in_array('required', $rules, true))) {

            return $request->has($this->attribute);

        }

        return in_array('required', $rules, true);
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

    public function withData($data): self
    {
        $this->data[] = $data;

        return $this;
    }

    private function resolveData(): array
    {
        return collect($this->data)->flatMap(fn($value) => value($value))->toArray();
    }

    public function jsonSerialize(): array
    {
        return array_merge([
            'label' => $this->label,
            'attribute' => $this->attribute,
            'value' => $this->value,
            'component' => $this->component(),
            'additionalInformation' => $this->resolveAdditionalInformation(),
        ], $this->resolveData());
    }

}
