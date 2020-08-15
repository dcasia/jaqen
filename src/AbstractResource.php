<?php

namespace DigitalCreative\Dashboard;

use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Traits\ResolveFieldsTrait;
use DigitalCreative\Dashboard\Traits\ResolveFiltersTrait;
use DigitalCreative\Dashboard\Traits\ResolveUriKey;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractResource
{

    use ResolveFieldsTrait;
    use ResolveFiltersTrait;
    use ResolveUriKey;

    private BaseRequest $request;

    public function __construct(BaseRequest $request)
    {
        $this->request = $request;
    }

    public function getModel(): Model
    {
        return new static::$model;
    }

    public function detail(): array
    {
        $model = $this->findResource();

        return [
            'key' => $model->getKey(),
            'fields' => $this->resolveFieldsUsingModel($model)
        ];
    }

    public function create(): void
    {

        $bag = new FieldsData();

        $fields = $this->resolveFields();

        $this->validateFields($fields);

        $callbacks = $this->filterNonUpdatableFields($fields)
                          ->map(fn(AbstractField $field) => $field->fillUsingRequest($bag, $this->request));

        $this->repository()->create($bag);

        $callbacks->filter()->each(fn(callable $function) => $function());

    }

    public function update(): bool
    {

        $fields = $this->filterNonUpdatableFields(
            $this->resolveFieldsUsingRequest($this->request)
        );

        $this->validateFields($fields);

        return $this->repository()->updateResource(
            $this->findResource(), $fields->pluck('value', 'attribute')->toArray()
        );
    }

    private function findResource(): ?Model
    {
        return once(function () {
            return $this->repository()
                        ->findByKey($this->request->route('key'));
        });
    }

    public function repository(): ResourceRepository
    {
        return app(ResourceRepository::class, [ 'model' => $this->getModel() ]);
    }

    public function index(): array
    {

        $fields = $this->resolveFields();

        $filters = new FilterCollection($this->resolveFilters(), $this->getRequest()->query('filters'));

        $total = $this->repository()->count($filters);

        $resources = $this->repository()
                          ->findCollection($filters, $this->request->query('page', 1))
                          ->map(static function (Model $model) use ($fields) {

                              return [
                                  'key' => $model->getKey(),
                                  'fields' => $fields->map(fn(AbstractField $field) => $field->resolve($model)->jsonSerialize())
                              ];

                          });

        return [
            'total' => $total,
            'resources' => $resources
        ];

    }

}
