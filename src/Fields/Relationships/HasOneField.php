<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Fields\Relationships;

use DigitalCreative\Dashboard\Concerns\WithEvents;
use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use DigitalCreative\Dashboard\Traits\EventsTrait;
use Illuminate\Database\Eloquent\Model;

class HasOneField extends BelongsToField implements WithEvents
{

    use EventsTrait;

    public function __construct(string $label, string $relation = null, string $relatedResource = null)
    {
        parent::__construct($label, $relation, $relatedResource);

        $this->afterCreate(function(User $model) {

            $resource = $this->getRelatedResource();

            $foreignerKey = $this->getRelationForeignKeyName($model);

            $requestData = array_merge(
                $this->request->input($this->relationAttribute), [ $foreignerKey => $model->getKey() ]
            );

            $cloneRequest = $this->request->duplicate($this->request->query(), $requestData);

            $fields = $resource->filterNonUpdatableFields($resource->resolveFields($cloneRequest, $this->relatedFieldsFor))
                               ->push(new EditableField('__injected__', $foreignerKey))
                               ->map(fn(AbstractField $field) => $field->resolveValueFromRequest($cloneRequest));

            $model->setRelation(
                $this->relationAttribute, $fields->persist($resource, $cloneRequest)
            );

        });
    }

    private function getRelationForeignKeyName(Model $model): string
    {
        return $model->{$this->relationAttribute}()->getForeignKeyName();
    }

    public function isMissing(): bool
    {
        return true;
    }

    protected function getSettings(): array
    {
        return $this->getRelatedResourcePayload();
    }

    public function resolveValueFromModel(Model $model, BaseRequest $request): BelongsToField
    {
        $this->model = $model;

        $relation = $model->getRelation($this->getRelationAttribute());

        if ($relation instanceof Model) {

            return $this->setValue($relation->getKey(), $request);

        }

        return $this;

    }

    public function getRelationAttributeKey(): string
    {
        return $this->relationAttribute;
    }

}
