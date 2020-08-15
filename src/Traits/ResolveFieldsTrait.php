<?php

namespace DigitalCreative\Dashboard\Traits;


use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\FieldsData;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Trait ResolveFieldsTrait
 *
 * @property BaseRequest $request
 *
 * @package DigitalCreative\Dashboard\Traits
 */
trait ResolveFieldsTrait
{

    public array $resourceListingFields = [ '*' ];
    public array $resourceDetailFields = [ '*' ];
    public array $resourceCreateFields = [ '*' ];

    public function fields(): array
    {
        return [];
    }

    /**
     * Resolve fields and remove every field that is not necessary for this given request
     *
     * @return \Illuminate\Support\Collection
     */
    private function resolveFields(): Collection
    {
        return once(function () {
            return collect($this->fields())
                ->filter(function (AbstractField $field) {

                    $fields = [ '*' ];

                    $request = $this->getRequest();

                    if ($request->isUpdate()) {

                        $fields = $request->keys();

                    } else if ($request->isCreate()) {

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
        return $this->resolveFields()->each(fn(AbstractField $field) => $field->resolve($model));
    }

    private function resolveFieldsUsingRequest(BaseRequest $request): Collection
    {
        return $this->resolveFields()->each(fn(AbstractField $field) => $field->resolveFromRequest($request));
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
                $field->attribute => $field->resolveRules($request)
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

}
