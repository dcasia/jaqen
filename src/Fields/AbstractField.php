<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Fields;

use DigitalCreative\Dashboard\FieldsData;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Traits\MakeableTrait;
use DigitalCreative\Dashboard\Traits\ResolveRulesTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class AbstractField implements JsonSerializable
{

    use ResolveRulesTrait;
    use MakeableTrait;

    /**
     * @var string|int|null
     */
    public $value;

    public string $label;
    public string $attribute;
    public ?array $additionalInformation = null;
    public bool $dirty = false;

    public function __construct(string $label, string $attribute = null)
    {
        $this->label = $label;
        $this->attribute = $attribute ?? Str::slug($label);
    }

    /**
     * @param BaseRequest $request
     * @param Model $model
     *
     * @return AbstractField
     */
    public function resolveUsingModel(BaseRequest $request, Model $model): self
    {
        return $this->setValue($model->getAttribute($this->attribute));
    }

    /**
     * @param BaseRequest $request
     *
     * @return AbstractField
     */
    public function resolveUsingRequest(BaseRequest $request): self
    {
        return $this->setValue($request->input($this->attribute));
    }

    public function setValue($value): self
    {
        $this->dirty = $this->value !== $value;
        $this->value = $value;

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

    public function jsonSerialize(): array
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
