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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
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

        $this->afterUpdate(function(Model $model) {

            $resource = $this->getRelatedResource();

            $this->updateRelatedModels($resource, $model, $this->request);

        });

        $this->afterCreate(function(Model $model) {

            $resource = $this->getRelatedResource();

            [ $models, $pivotAttributes ] = $this->createRelatedModels($resource, $this->request);

            $resource->repository()->saveMany(
                $this->getRelationInstance($model), $models, $pivotAttributes
            );

            $model->setRelation($this->relationAttribute, collect($models));

        });
    }

    private function updateRelatedModels(AbstractResource $resource, Model $model, BaseRequest $request): void
    {

        $relatedData = $request->input($this->relationAttribute, []);
        $pivotFields = $resource->filterNonUpdatableFields($this->resolvePivotFields());

        /**
         * Create the related models
         */
        foreach ($relatedData as $data) {

            $key = (string) data_get($data, 'key');
            $fieldsData = data_get($data, 'fields', []);
            $pivotFieldsData = data_get($data, 'pivotFields', []);

            /**
             * Store Model
             */
            $fieldsRequest = $request->duplicate($request->query(), $fieldsData);

            $fields = $resource->filterNonUpdatableFields($resource->resolveFields($fieldsRequest, $this->relatedFieldsFor))
                               ->map(fn(AbstractField $field) => $field->resolveValueFromRequest($fieldsRequest));

            $relatedModel = $resource->repository()->findByKey($key);

            $fields->update($resource, $relatedModel, $fieldsRequest);

            /**
             * Resolve Pivot Attributes
             */
            $pivotAttributes = $this->getPivotAttributeData($pivotFields, $request, $pivotFieldsData);

            $resource->repository()
                     ->updatePivot($this->getRelationInstance($model), $key, $pivotAttributes);

        }

    }

    private function getPivotAttributeData(FieldsCollection $pivotFields, BaseRequest $request, array $pivotFieldsData): array
    {
        $pivotRequest = $request->duplicate($request->query(), $pivotFieldsData);

        return $pivotFields->map(fn(AbstractField $field) => $field->resolveValueFromRequest($pivotRequest))
                           ->resolveData();
    }

    private function createRelatedModels(AbstractResource $resource, BaseRequest $request): array
    {

        $models = [];
        $pivotAttributes = [];

        $relatedData = $request->input($this->relationAttribute, []);
        $pivotFields = $resource->filterNonUpdatableFields($this->resolvePivotFields());

        /**
         * Create the related models
         */
        foreach ($relatedData as $data) {

            $fieldsData = data_get($data, 'fields', []);
            $pivotFieldsData = data_get($data, 'pivotFields', []);

            /**
             * Store Model
             */
            $fieldsRequest = $request->duplicate($request->query(), $fieldsData);

            $fields = $resource->filterNonUpdatableFields($resource->resolveFields($fieldsRequest, $this->relatedFieldsFor))
                               ->map(fn(AbstractField $field) => $field->resolveValueFromRequest($fieldsRequest));

            $models[] = $fields->persist($resource, $fieldsRequest);

            /**
             * Resolve Pivot Attributes
             */
            $pivotAttributes[] = $this->getPivotAttributeData($pivotFields, $request, $pivotFieldsData);

        }

        return [ $models, $pivotAttributes ];

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

    public function getRelationInstance(Model $model): BelongsToMany
    {
        return once(fn() => $model->{$this->relationAttribute}());
    }

    public function getPivotAccessor(Model $model = null): string
    {
        return $this->getRelationInstance($model ?? $this->model)->getPivotAccessor();
    }

    public function setPivotFields($fields): self
    {
        $this->pivotFields = $fields;

        return $this;
    }

    private function resolveRelatedPivotFieldsData(FieldsCollection $pivotFields, Model $model): FieldsCollection
    {
        $pivotAccessor = $this->getPivotAccessor();

        return $pivotFields->getResolvedFieldsData(
            $model->getRelation($pivotAccessor), $this->request
        );
    }

    protected function getRelatedResourcePayload(): array
    {

        $payload = [];

        if ($relatedResource = $this->resolveRelatedResource()) {

            $payload['relatedResource'] = $relatedResource->getDescriptor();

            $fields = $relatedResource->resolveFields($this->request, $this->relatedFieldsFor);
            $pivotFields = $this->resolvePivotFields();

            if ($this->request->isSchemaFetching()) {

                $payload['relatedResource']['fields'] = $fields;
                $payload['relatedResource']['pivotFields'] = $pivotFields;

                return $payload;

            }

            /**
             * @var Collection $models
             */
            $models = $this->getRelatedModelInstance();

            if (!$models instanceof Collection) {

                throw new RuntimeException('Invalid relationship type.');

            }

            /**
             * @var Model $model
             */
            foreach ($models as $model) {

                $payload['relatedResource']['resources'][] = [
                    'key' => $model->getKey(),
                    'fields' => $fields->hydrate($model, $this->request)->toArray(),
                    'pivotFields' => $this->resolveRelatedPivotFieldsData($pivotFields, $model),
                ];

            }

        }

        return $payload;

    }

}
