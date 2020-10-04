<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Fields;

use DigitalCreative\Dashboard\FieldsData;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
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
    public ?array $additionalInformation = null;

    protected BaseRequest $request;

    /**
     * @var callable|mixed
     */
    private $defaultCallback;

    /**
     * @var callable|mixed
     */
    private $readOnly = false;

    public function __construct(string $label, string $attribute = null)
    {
        $this->label = $label;
        $this->attribute = $attribute ?? $this->generateAttribute($label);
    }

    /**
     * @param bool|callable $state
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

    public function setRequest(BaseRequest $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function isDirty(): bool
    {
        return $this->dirty;
    }

    public function isRequired(BaseRequest $request): bool
    {
        return in_array('required', $this->resolveRules($request), true);
    }

    /**
     * Return a callback to perform any operation after the resource has been saved to the database
     *
     * @param FieldsData $dataBag
     * @param array $data
     * @param BaseRequest $request
     * @return callable|null
     */
    public function fill(FieldsData $dataBag, array $data, BaseRequest $request): ?callable
    {
        $dataBag->setAttribute($this->attribute, data_get($data, $this->attribute));

        return null;
    }

    public function getAdditionalInformation(): ?array
    {
        return $this->additionalInformation;
    }

    public function withAdditionalInformation(array $options): self
    {
        $this->additionalInformation = array_merge($this->additionalInformation ?? [], $options);

        return $this;
    }

    public function component(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    /**
     * @param mixed $value
     *
     * @return AbstractField
     */
    public function default($value): self
    {
        $this->defaultCallback = $value;

        return $this;
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
            'additionalInformation' => $this->getAdditionalInformation(),
        ];
    }

}
