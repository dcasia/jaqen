<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Fields\Relationships;

use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Services\Fields\Fields\AbstractField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class BelongsToField extends Relationship
{

    protected ?Model $model = null;
    protected BaseRequest $request;
    protected ?string $relationAttribute;

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
        parent::__construct($label, $this->getRelationAttributeKey());
    }

    public function getRelationAttributeKey(): string
    {
        return $this->relationAttribute . '_id';
    }

    public function boot($resource, BaseRequest $request): void
    {
        parent::boot($resource, $request);

        $this->request = $request;
        $this->parentResource->with([ $this->relationAttribute ], false);
    }

    public function resolveValueFromModel(Model $model, BaseRequest $request): BelongsToField
    {
        $this->model = $model;
        $this->request = $request;

        return $this->setValue($model->getAttributeValue($this->attribute), $request);
    }

    protected function resolveRelatedResource(): ?AbstractResource
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

    /**
     * @return Model|Collection|null
     */
    protected function getRelatedModelInstance()
    {

        if ($this->model === null) {

            return null;

        }

        if (method_exists($this->model, $this->relationAttribute)) {

            if ($this->model->relationLoaded($this->relationAttribute)) {

                if ($relation = $this->model->getRelation($this->relationAttribute)) {

                    return $relation;

                }

                return null;

            }

            throw new RuntimeException(sprintf('Relationship { %s } was not loaded.', $this->relationAttribute));

        }

        throw new RuntimeException(sprintf('Relation { %s } does not exist.', $this->relationAttribute));

    }

    public function resolveSearchCallback(): callable
    {

        if (is_callable($this->searchableCallback)) {

            return $this->searchableCallback;

        }

        /**
         * @todo try to abstract this call to the repository
         * @todo instead of $request->query('id') try $request->query(RelatedModel::getKeyName()) in case user dont call the key as ID
         */
        return static function (Builder $builder, BaseRequest $request): Builder {

            return $builder->when($request->query('id'), fn(Builder $builder, string $search) => $builder->whereKey($search))
                           ->limit(10);

        };

    }

    /**
     * @todo An idea here could be if we also could pass an array like: [ name, age ] which we could use as the fields to search for using the default search callback
     */
    public function searchable(callable|bool $callback = true): self
    {
        $this->searchableCallback = $callback;

        return $this;
    }

    public function isSearchable(): bool
    {
        return is_callable($this->searchableCallback) ? true : $this->searchableCallback;
    }

    protected function getRelatedResourcePayload(): array
    {

        $payload = [];

        if ($relatedResource = $this->resolveRelatedResource()) {

            $relatedModel = $this->getRelatedModelInstance();

            $payload['relatedResource'] = $relatedResource->getDescriptor();

            $fields = $relatedResource->resolveFields($this->request, $this->relatedFieldsFor);

            $fields->when($relatedModel)
                   ->each(fn(AbstractField $field) => $field->hydrateFromModel($relatedModel, $this->request));

            $payload['relatedResource']['fields'] = $fields->toArray();

        }

        return $payload;

    }

    protected function getSettings(): array
    {
        $data = [
            'searchable' => $this->isSearchable(),
        ];

        return array_merge($data, $this->getRelatedResourcePayload());
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), $this->getSettings());
    }

}
