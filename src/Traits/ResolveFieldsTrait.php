<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Traits;

use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Fields\ReadOnlyField;
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

    /**
     * @todo Delete this makes no sense more after fieldsFor functionality
     * @var array|string[]
     */
    public array $resourceListingFields = [ '*' ];
    public array $resourceCreateFields = [ '*' ];

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

    /**
     * Resolve fields and remove every field that is not necessary for this given request
     *
     * @return Collection
     */
    public function resolveFields(): Collection
    {
        return once(function() {

            $request = $this->getRequest();
            $for = Str::camel($request->input('fieldsFor', 'fields'));

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
                ->each
                ->setRequest($request)
                ->filter(function(AbstractField $field) use ($request) {

                    $fields = [ '*' ];

                    if ($request->isCreate()) {

                        $fields = $this->resourceCreateFields;

                    } else if ($request->isListing()) {

                        $fields = $this->resourceListingFields;

                    }

                    if (in_array('*', $fields, true)) {

                        return true;

                    }

                    return in_array($field->attribute, $fields, true);

                })
                ->values();
        });
    }

    private function resolveFieldsUsingModel(Model $model): Collection
    {
        return $this->resolveFields()->each(fn(AbstractField $field) => $field->resolveUsingModel($this->getRequest(), $model));
    }

    private function resolveFieldsUsingRequest(BaseRequest $request): Collection
    {
        return $this->resolveFields()->each(fn(AbstractField $field) => $field->resolveUsingRequest($request));
    }

    private function filterNonUpdatableFields(Collection $fields): Collection
    {
        return $fields->filter(fn(AbstractField $field) => !$field instanceof ReadOnlyField);
    }

    private function validateFields(Collection $fields): array
    {
        $request = $this->getRequest();

        $rules = $fields
            ->mapWithKeys(fn(AbstractField $field) => [
                $field->attribute => $field->resolveRules($request),
            ])
            ->filter()
            ->toArray();

        return $request->validate($rules);

    }

    public function getFieldsDataFromRequest(): FieldsData
    {

        $data = new FieldsData();

        $fields = $this->resolveFields();

        $this->validateFields($fields);

        $request = $this->getRequest();

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
        return $this->resolveFields()
                    ->first(static function(AbstractField $field) use ($attribute) {

                        if ($field instanceof BelongsToField) {

                            return $field->getRelationAttribute() === $attribute;

                        }

                        return $field->attribute === $attribute;

                    });
    }

}
