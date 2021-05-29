<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Fields\Traits;

use DigitalCreative\Jaqen\Concerns\BehaveAsPanel;
use DigitalCreative\Jaqen\Fields\Relationships\BelongsToField;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Services\Fields\Fields\AbstractField;
use DigitalCreative\Jaqen\Services\Fields\Fields\FieldsCollection;
use DigitalCreative\Jaqen\Services\Fields\FieldsData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LogicException;
use RuntimeException;

/**
 * Trait ResolveFieldsTrait
 *
 * @property BaseRequest $request
 *
 * @package DigitalCreative\Dashboard\Traits
 */
trait ResolveFieldsTrait
{

    public array $fields = [];

    public function fieldsFor(string $name, callable $callable): self
    {
        $this->fields[Str::camel($name)] = $callable;

        return $this;
    }

    public function fields(): array
    {
        return [];
    }

    public function resolveNonUpdatableValidatedFields(BaseRequest $request): array
    {
        return [
            $fields = $this->filterNonUpdatableFields($this->resolveFields($request)),
            $this->validateFields($fields, $request),
        ];
    }

    public function resolveValidatedFields(BaseRequest $request): array
    {
        return [
            $fields = $this->resolveFields($request),
            $this->validateFields($fields, $request),
        ];
    }

    /**
     * Resolve fields and remove every field that is not necessary for this given request
     *
     * @param BaseRequest $request
     * @param string|null $for
     *
     * @return FieldsCollection
     */
    public function resolveFields(BaseRequest $request, ?string $for = null): FieldsCollection
    {
        return once(function () use ($request, $for) {

            $for = $for ?? Str::camel($request->input('fieldsFor', 'fields'));

            $only = $request->input('only');
            $except = $request->input('except');

            /**
             * If fields has been set through ->fieldsFor()
             */
            if (array_key_exists($for, $this->fields)) {

                $value = value($this->fields[$for]);

                if (is_string($value) && class_exists($value)) {

                    $instance = resolve($value);

                    if (is_callable($instance)) {

                        $fields = $instance();

                        if (!is_array($fields)) {

                            throw new RuntimeException('Invokable class should return an array.');

                        }

                    } else {

                        throw new RuntimeException('Invalid invokable class.');

                    }

                } else if (is_array($value)) {

                    $fields = $value;

                } else {

                    throw new LogicException('Invalid value given.');

                }

            } else {

                $method = "fieldsFor$for";

                if (method_exists($this, $method)) {

                    $fields = $this->$method();

                } else {

                    $fields = $this->fields();

                }

            }

            return (new FieldsCollection($fields))
                ->when($request->isStoringResourceToDatabase(), function (FieldsCollection $fields) {

                    return $fields->flatMap(function (AbstractField $field) {

                        if ($field instanceof BehaveAsPanel) {

                            return $field->getFields();

                        }

                        return [ $field ];

                    });

                })
                ->when($only, function (FieldsCollection $fields, string $only) {
                    return $fields->filter(
                        fn(AbstractField $field) => $this->stringContains($only, $field->attribute)
                    );
                })
                ->when($except, function (FieldsCollection $fields, string $except) {
                    return $fields->filter(
                        fn(AbstractField $field) => !$this->stringContains($except, $field->attribute)
                    );
                })
                ->each(fn(AbstractField $field) => $field->boot($this, $request))
                ->values();

        });
    }

    private function stringContains(string $items, string $attribute): bool
    {
        return Str::of($items)
                  ->explode(',')
                  ->map(fn(string $item) => trim($item))
                  ->contains($attribute);
    }

    public function resolveFieldsUsingModel(Model $model, BaseRequest $request): FieldsCollection
    {
        return $this->resolveFields($request)->getResolvedFieldsData($model, $request);
    }

    public function filterNonUpdatableFields(FieldsCollection $fields): FieldsCollection
    {
        return $fields->filter(fn(AbstractField $field) => $field->isReadOnly() === false);
    }

    private function validateFields(FieldsCollection $fields, BaseRequest $request): array
    {

        $rules = $fields
            ->mapWithKeys(fn(AbstractField $field) => [
                $field->attribute => $field->resolveRules($request),
            ])
            ->toArray();

        return $request->validate($rules);

    }

    public function getFieldsDataFromRequest(): FieldsData
    {

        $request = $this->getRequest();

        $fields = $this->resolveFields($request);

        $validated = $this->validateFields($fields, $request);

        $data = $this->filterNonUpdatableFields($fields)
                     ->map(fn(AbstractField $field) => $field->resolveValueFromArray($validated, $request))
                     ->pluck('value', 'attribute');

        return new FieldsData($data);

    }

    private function getRequest(): BaseRequest
    {
        return $this->request ?? app(BaseRequest::class);
    }

    public function addDefaultFields(AbstractField ...$fields): self
    {
        $this->fields['fields'] = array_merge($this->fields, $fields);

        return $this;
    }

    public function findFieldByAttribute(BaseRequest $request, string $attribute): ?AbstractField
    {
        return $this->resolveFields($request)
                    ->first(function (AbstractField $field) use ($attribute) {

                        if ($field instanceof BelongsToField) {

                            return $field->getRelationAttribute() === $attribute;

                        }

                        return $field->attribute === $attribute;

                    });
    }

}
