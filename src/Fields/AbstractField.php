<?php

namespace DigitalCreative\Dashboard\Fields;

use DigitalCreative\Dashboard\FieldsData;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Traits\ResolveRulesTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class AbstractField implements JsonSerializable
{

    use ResolveRulesTrait;

    public string $label;
    public string $attribute;
    public ?string $value = null;
    public ?array $additionalInformation = null;

    public function __construct(string $label, string $attribute = null)
    {
        $this->label = $label;
        $this->attribute = Str::slug($attribute ?? $label);
    }

    public function clone(): self
    {
        return clone $this;
    }

    /**
     * @param Model $model
     *
     * @return AbstractField
     */
    public function resolve(Model $model): self
    {
        return $this->setValue($model->getAttribute($this->attribute));
    }

    /**
     * @param BaseRequest $request
     *
     * @return AbstractField
     */
    public function resolveFromRequest(BaseRequest $request): self
    {
        return $this->setValue($request->input($this->attribute));
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Return a callback to perform any operation after the resource has been saved to the database
     *
     * @param FieldsData $dataBag
     * @param BaseRequest $request
     *
     * @return callable|null
     */
    public function fillUsingRequest(FieldsData $dataBag, BaseRequest $request): ?callable
    {

        $dataBag->setAttribute($this->attribute, $request->input([ $this->attribute ], null));

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

    public function jsonSerialize()
    {
        return [
            'label' => $this->label,
            'attribute' => $this->attribute,
            'value' => $this->value,
            'component' => $this->component(),
            'additionalInformation' => $this->getAdditionalInformation()
        ];
    }

}
