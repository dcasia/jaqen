<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Traits;

use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\FieldsData;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
     * @return Collection
     */
    public function resolveFields(BaseRequest $request): Collection
    {
        return once(function() use ($request) {

            $for = Str::camel($request->input('fieldsFor', 'fields'));

            $only = $request->input('only', null);
            $except = $request->input('except', null);

            /**
             * If fields has been set through ->fieldsFor()
             */
            if (array_key_exists($for, $this->fields)) {

                $fields = value($this->fields[$for]);

            } else {

                $method = "fieldsFor$for";

                if (method_exists($this, $method)) {

                    $fields = $this->$method();

                } else {

                    $fields = $this->fields();

                }

            }

            return collect($fields)
                ->when($only, function(Collection $fields, string $only) {
                    return $fields->filter(
                        fn(AbstractField $field) => $this->stringContains($only, $field->attribute)
                    );
                })
                ->when($except, function(Collection $fields, string $except) {
                    return $fields->filter(
                        fn(AbstractField $field) => !$this->stringContains($except, $field->attribute)
                    );
                })
                ->map(fn(AbstractField $field) => $field->setRequest($request)->resolve())
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

    private function resolveFieldsUsingModel(Model $model): Collection
    {
        $request = $this->getRequest();

        return $this->resolveFields($request)
                    ->each(fn(AbstractField $field) => $field->resolveUsingModel($request, $model));
    }

    private function resolveFieldsUsingRequest(BaseRequest $request): Collection
    {
        return $this->resolveFields($request)
                    ->each(fn(AbstractField $field) => $field->resolveUsingRequest($request));
    }

    public function filterNonUpdatableFields(Collection $fields): Collection
    {
        return $fields->filter(fn(AbstractField $field) => $field->isReadOnly() === false);
    }

    private function validateFields(Collection $fields, BaseRequest $request): array
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

        $data = new FieldsData();

        $request = $this->getRequest();

        $fields = $this->resolveFields($request);

        $this->validateFields($fields, $request);

        $this->filterNonUpdatableFields($fields)
             ->map(fn(AbstractField $field) => $field->fillUsingRequest($data, $request));

        return $data;

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

    public function findFieldByAttribute(string $attribute): ?AbstractField
    {
        return $this->resolveFields($this->request)
                    ->first(static function(AbstractField $field) use ($attribute) {

                        if ($field instanceof BelongsToField) {

                            return $field->getRelationAttribute() === $attribute;

                        }

                        return $field->attribute === $attribute;

                    });
    }

}
