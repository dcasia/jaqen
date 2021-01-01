<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Fields\Relationships;

use DigitalCreative\Jaqen\Concerns\WithEvents;
use DigitalCreative\Jaqen\Exceptions\BelongsToManyException;
use DigitalCreative\Jaqen\Fields\AbstractField;
use DigitalCreative\Jaqen\FieldsCollection;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Resources\AbstractResource;
use DigitalCreative\Jaqen\Traits\EventsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
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

    /**
     * @param AbstractResource $resource
     * @param Model $model
     * @param BaseRequest $request
     * @throws BelongsToManyException
     */
    private function updateRelatedModels(AbstractResource $resource, Model $model, BaseRequest $request): void
    {

        $relatedData = $request->input($this->relationAttribute, []);
        $pivotFields = $resource->filterNonUpdatableFields($this->resolvePivotFields());

        /**
         * Create the related models
         */
        foreach ($relatedData as $data) {

            $key = (string) data_get($data, 'key');

            /**
             * Store Model
             */
            [ $fields, $fieldsRequest, $pivotRequest ] = $this->processFields($request, $resource, $data, $pivotFields);

            $relatedModel = $resource->repository()->findByKey($key);

            $fields->update($resource, $relatedModel, $fieldsRequest);

            /**
             * Resolve Pivot Attributes
             */
            $pivotAttributes = $this->getPivotAttributeData($pivotFields, $pivotRequest);

            $resource->repository()
                     ->updatePivot($this->getRelationInstance($model), $key, $pivotAttributes);

        }

    }

    /**
     * @param FieldsCollection $pivotFields
     * @param BaseRequest $request
     * @return array
     */
    private function getPivotAttributeData(FieldsCollection $pivotFields, BaseRequest $request): array
    {
        return $pivotFields->map(fn(AbstractField $field) => $field->resolveValueFromRequest($request))
                           ->resolveData();
    }

    /**
     * @param AbstractResource $resource
     * @param BaseRequest $request
     * @return array[]
     * @throws BelongsToManyException
     */
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

            /**
             * Store Model
             */
            [ $fields, $fieldsRequest, $pivotRequest ] = $this->processFields($request, $resource, $data, $pivotFields);

            $models[] = $fields->store($resource, $fieldsRequest);

            /**
             * Resolve Pivot Attributes
             */
            $pivotAttributes[] = $this->getPivotAttributeData($pivotFields, $pivotRequest);

        }

        return [ $models, $pivotAttributes ];

    }

    /**
     * @param BaseRequest $request
     * @param AbstractResource $resource
     * @param array $requestData
     * @param FieldsCollection $pivotFields
     *
     * @return array
     *
     * @throws BelongsToManyException
     */
    private function processFields(BaseRequest $request, AbstractResource $resource, array $requestData, FieldsCollection $pivotFields): array
    {

        $fieldsData = data_get($requestData, 'fields', []);
        $pivotFieldsData = data_get($requestData, 'pivotFields', []);

        $fieldsRequest = $request::createFromBase($request)->replace($fieldsData);
        $pivotRequest = $request::createFromBase($request)->replace($pivotFieldsData);

        $fields = $resource->resolveFields($fieldsRequest, $this->relatedFieldsFor);

        $this->validateFields([ 'fields' => [ $fields, $fieldsRequest ], 'pivotFields' => [ $pivotFields, $pivotRequest ] ]);

        $fields = $resource->filterNonUpdatableFields($fields)
                           ->map(fn(AbstractField $field) => $field->resolveValueFromRequest($fieldsRequest));

        return [
            $fields,
            $fieldsRequest,
            $pivotRequest,
        ];

    }

    /**
     * @param array $fieldsGroup
     * @throws BelongsToManyException
     */
    private function validateFields(array $fieldsGroup): void
    {

        $exceptions = [];

        /**
         * @var string $attributeKey
         * @var FieldsCollection $fields
         * @var BaseRequest $request
         */
        foreach ($fieldsGroup as $attributeKey => [$fields, $request]) {

            try {

                $fields->validate($request);

            } catch (ValidationException $exception) {

                $exceptions[$attributeKey] = $exception;

            }

        }

        if (count($exceptions)) {

            throw BelongsToManyException::fromValidationExceptions([
                $this->getRelationAttribute() => $exceptions,
            ]);

        }

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
