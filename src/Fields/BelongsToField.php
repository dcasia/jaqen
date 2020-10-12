<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Fields;

use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

class BelongsToField extends AbstractField
{

    /**
     * @var callable|array
     */
    private $optionsCallback;
    private ?string $relationAttribute;
    private ?string $relatedResource = null;
    private ?string $relatedFieldsFor = null;

    protected Model $model;
    protected BaseRequest $request;

    /**
     * @var callable|bool
     */
    private $searchableCallback = false;

    public function __construct(string $label, string $relation = null, string $relatedResource = null)
    {
        $this->relationAttribute = $relation ?? Str::camel($label);

        if ($relatedResource) {
            $this->setRelatedResource($relatedResource);
        }

        /**
         * @todo
         * Support specifying a custom logic for the _id prefix.. because the user could have a relationship that has
         * a completely different key from model_id convention
         * Perhaps the best approach would be replacing the $relation as on set on the model to the attribute as the one that exists on the database
         */
        parent::__construct($label, $this->relationAttribute . '_id');
    }

    public function boot($resource): void
    {
        parent::boot($resource);

        $this->parentResource->with([ $this->relationAttribute ], false);
    }

    public function resolveValueFromModel(Model $model, BaseRequest $request): BelongsToField
    {
        $this->model = $model;
        $this->request = $request;

        return $this->setValue($model->getAttributeValue($this->attribute), $request);
    }

    protected function resolveOptions(BaseRequest $request): ?array
    {
        if (is_callable($this->optionsCallback)) {

            return call_user_func($this->optionsCallback, $request);

        }

        return $this->optionsCallback;
    }

    private function resolveRelatedResource(): ?AbstractResource
    {
        return once(function () {

            if ($this->relatedResource) {

                if (is_subclass_of($this->relatedResource, AbstractResource::class) === false) {

                    throw new RuntimeException('Please provide a valid resource class.');

                }

                return resolve($this->relatedResource);

            }

            return null;

        });
    }

    public function getRelationAttribute(): string
    {
        return $this->relationAttribute;
    }

    public function getRelatedResource(): AbstractResource
    {
        return $this->resolveRelatedResource();
    }

    private function getRelatedModelInstance(): ?Model
    {

        if (method_exists($this->model, $this->relationAttribute)) {

            if ($this->model->relationLoaded($this->relationAttribute)) {

                if ($relation = $this->model->getRelation($this->relationAttribute)) {

                    return $relation;

                }

                return null;

            }

            throw new RuntimeException(sprintf('Relationship { %s } was not loaded.', $this->relationAttribute));

        }

        throw new RuntimeException(
            sprintf(
                'Relation { %s } does not exist. Please setup the belongsTo relation correctly on your model.', $this->relationAttribute
            )
        );

    }

    /**
     * @param array|callable $options
     * @return $this
     */
    public function options($options): self
    {
        $this->optionsCallback = $options;

        return $this;
    }

    public function setRelatedResourceFieldsFor(string $fieldsFor): self
    {
        $this->relatedFieldsFor = $fieldsFor;

        return $this;
    }

    public function setRelatedResource(string $relatedResource, string $fieldsFor = null): self
    {
        $this->relatedResource = $relatedResource;

        if ($fieldsFor) {
            $this->setRelatedResourceFieldsFor($fieldsFor);
        }

        return $this;
    }

    public function resolveSearchCallback(): callable
    {

        if (is_callable($this->searchableCallback)) {

            return $this->searchableCallback;

        }

        /**
         * @todo try to abstract this call to the repository
         * @todo instance of $request->query('id') try $request->query(RelatedModel::getKeyName()) in case user dont call the key as ID
         */
        return static function (Builder $builder, BaseRequest $request): Builder {
            return $builder->when($request->query('id'), fn(Builder $builder, string $search) => $builder->whereKey($search))
                           ->limit(10);
        };

    }

    /**
     * @param callable|bool $callback
     *
     * @return $this
     */
    public function searchable($callback = true): self
    {
        $this->searchableCallback = $callback;

        return $this;
    }

    public function isSearchable(): bool
    {
        return is_callable($this->searchableCallback) ? true : $this->searchableCallback;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'label' => $this->label,
            'attribute' => $this->attribute,
            'value' => $this->value,
            'component' => $this->component(),
            'additionalInformation' => null,
            'settings' => [
                'searchable' => $this->isSearchable(),
                'options' => $this->resolveOptions($this->request),
            ],
        ];

        if ($relatedResource = $this->resolveRelatedResource()) {

            $relatedModel = $this->getRelatedModelInstance();

            $data['additionalInformation'] = $this->resolveAdditionalInformation($relatedModel);
            $data['settings']['relatedResource'] = $relatedResource->getDescriptor();

            $fields = $relatedResource->resolveFields($this->request, $this->relatedFieldsFor);

            $fields->when($relatedModel)
                   ->each(fn(AbstractField $field) => $field->hydrateFromModel($relatedModel, $this->request));

            $data['settings']['relatedResource']['fields'] = $relatedResource->resolveFields(
                $this->request, $this->relatedFieldsFor
            );

        }

        return $data;
    }

}
