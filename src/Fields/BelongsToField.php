<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Fields;

use Closure;
use DigitalCreative\Dashboard\AbstractResource;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\CreateResourceRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

class BelongsToField extends AbstractField
{

    private ?Closure $extraRelationDataCallback = null;
    private ?string $relationAttribute;
    private ?string $resourceClass;

    public function __construct(string $label, string $relation = null, ?string $resource = null)
    {
        $this->relationAttribute = $relation ?? Str::snake($label);
        $this->resourceClass = $resource;

        parent::__construct($label, $this->relationAttribute . '_id');
    }

    /**
     * @param BaseRequest $request
     * @param Model $model
     *
     * @return BelongsToField
     */
    public function resolveUsingModel(BaseRequest $request, Model $model): BelongsToField
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

        return $this->setValue($model->getAttributeValue($this->attribute));

    }

    public function withExtraRelationData(callable $callback): self
    {
        $this->extraRelationDataCallback = $callback;

        return $this;
    }

    private function resolveValue(): array
    {

        if ($this->resourceClass) {

            if (is_subclass_of($this->resourceClass, AbstractResource::class) === false) {

                throw new RuntimeException('Please provide a valid resource class.');

            }

            /**
             * @var AbstractResource $resource
             */
            $resource = new $this->resourceClass(app(CreateResourceRequest::class));

            return [
                'belongsToId' => $this->value,
                'blueprint' => $resource->resolveFields()
            ];

        }

        return [
            'belongsToId' => $this->value,
        ];

    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [ 'value' => $this->resolveValue(), ]);
    }

}
