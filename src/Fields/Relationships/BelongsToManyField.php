<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Fields\Relationships;

use DigitalCreative\Dashboard\Concerns\WithEvents;
use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\FieldsCollection;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Traits\EventsTrait;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class BelongsToManyField extends BelongsToField implements WithEvents
{

    use EventsTrait;

    /**
     * @var array|callable
     */
    private $pivotFields;

    public function __construct(string $label, string $relation = null, string $relatedResource = null)
    {
        parent::__construct($label, $relation, $relatedResource);

        $this->afterCreate(function(Model $model) {

            $resource = $this->getRelatedResource();

            $models = $this->createRelatedModels($resource, $this->request);
            $pivotAttributes = $this->getPivotAttributes($resource, $this->request);

            /**
             * @todo run this check before creating the models above by using the request itself
             * as this data can be assumed by just looking into the request data itself
             */
            if ($this->usePivot() && count($models) !== count($pivotAttributes)) {

                throw new RuntimeException('Invalid attributes length.');

            }

            $resource->repository()->saveMany(
                $model->{$this->relationAttribute}(), $models, $pivotAttributes
            );

            $model->setRelation($this->relationAttribute, collect($models));

        });

    }

    private function usePivot(): bool
    {
        return $this->pivotFields !== null;
    }

    private function getPivotAttributes(AbstractResource $resource, BaseRequest $request): array
    {

        $pivotAttributes = [];
        $relatedPivotData = $request->input($this->getRelatedPivotAttribute(), []);

        /**
         * Attach all related models to resource
         */
        $pivotFields = $resource->filterNonUpdatableFields($this->resolvePivotFields());

        foreach ($relatedPivotData as $data) {

            $cloneRequest = $request->duplicate($request->query(), $data);

            $pivotAttributes[] = $pivotFields->map(fn(AbstractField $field) => $field->resolveValueFromRequest($cloneRequest))
                                             ->resolveData();

        }

        return $pivotAttributes;

    }

    private function createRelatedModels(AbstractResource $resource, BaseRequest $request): array
    {

        $models = [];
        $relatedData = $request->input($this->relationAttribute, []);

        /**
         * Create the related models
         */
        foreach ($relatedData as $data) {

            $cloneRequest = $request->duplicate($request->query(), $data);

            $fields = $resource->filterNonUpdatableFields($resource->resolveFields($cloneRequest, $this->relatedFieldsFor))
                               ->map(fn(AbstractField $field) => $field->resolveValueFromRequest($cloneRequest));

            $models[] = $fields->persist($resource, $cloneRequest);

        }

        return $models;

    }

    private function resolvePivotFields(): FieldsCollection
    {
        return new FieldsCollection(value($this->pivotFields));
    }

    public function getRelatedPivotAttribute(): string
    {
        return $this->relationAttribute . 'Pivot';
    }

    public function isMissing(): bool
    {
        return true;
    }

    public function getRelationAttributeKey(): string
    {
        return $this->relationAttribute;
    }

    public function setPivotFields($fields): self
    {
        $this->pivotFields = $fields;

        return $this;
    }

    private function getRelatedResourcePivotData(): array
    {
        return [
            'attribute' => $this->getRelatedPivotAttribute(),
            'fields' => $this->resolvePivotFields(),
        ];
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [ 'relatedResourcePivot' => $this->getRelatedResourcePivotData() ]);
    }

}
