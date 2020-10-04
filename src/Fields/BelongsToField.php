<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Fields;

use Closure;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use RuntimeException;

class BelongsToField extends AbstractField
{

    private ?Closure $extraRelationDataCallback = null;
    private ?Closure $optionsCallback = null;
    private ?string $relationAttribute;
    private ?string $relatedResource = null;

    /**
     * @var callable|bool
     */
    private $searchableCallback = false;

    public function __construct(string $label, string $relation = null)
    {
        $this->relationAttribute = $relation ?? Str::snake($label);

        parent::__construct($label, $this->relationAttribute . '_id');
    }

    public function resolveValueFromModel(Model $model, BaseRequest $request): BelongsToField
    {

        if ($this->extraRelationDataCallback) {

            $relation = $model->getAttribute($this->relationAttribute);

            if ($relation instanceof Model) {

                $this->withAdditionalInformation(
                    call_user_func($this->extraRelationDataCallback, $request, $relation)
                );

            } else {

                $this->withAdditionalInformation($relation);

            }

        }

        return $this->setValue($model->getAttributeValue($this->attribute), $request);

    }

    public function withExtraRelatedResourceData(callable $callback): self
    {
        $this->extraRelationDataCallback = $callback;

        return $this;
    }

    protected function resolveOptions(): ?array
    {
        if (is_callable($this->optionsCallback)) {

            return call_user_func($this->optionsCallback, $this->request);

        }

        return null;
    }

    private function resolveRelatedResource(): ?AbstractResource
    {
        return once(function() {

            if ($this->relatedResource) {

                if (is_subclass_of($this->relatedResource, AbstractResource::class) === false) {

                    throw new RuntimeException('Please provide a valid resource class.');

                }

                return new $this->relatedResource($this->request);

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

    public function getRelatedModel(AbstractResource $parentResource): Model
    {

        if ($resource = $this->resolveRelatedResource()) {

            return $resource->getModel();

        }

        $baseModel = $parentResource->getModel();

        if (method_exists($baseModel, $this->relationAttribute)) {

            $relation = $baseModel->{$this->relationAttribute}();

            if ($relation instanceof BelongsTo) {

                return $relation->getRelated();

            }

        }

        throw new RuntimeException('Could not determined the related model for this resource.');

    }

    public function options(callable $options): self
    {
        $this->optionsCallback = $options;

        return $this;
    }

    public function setRelatedResource(string $relatedResource): self
    {
        $this->relatedResource = $relatedResource;

        return $this;
    }

    public function resolveSearchCallback(): callable
    {

        if (is_callable($this->searchableCallback)) {

            return $this->searchableCallback;

        }

        return static function(Builder $builder, BaseRequest $request): Builder {
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
            'settings' => [
                'searchable' => $this->isSearchable(),
                'options' => $this->resolveOptions(),
            ],
        ];

        if ($resource = $this->resolveRelatedResource()) {

            $data['settings']['fields'] = $resource->resolveFields(app(BaseRequest::class))->toArray();

        }

        return array_merge(parent::jsonSerialize(), $data);
    }

}
