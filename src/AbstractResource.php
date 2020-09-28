<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard;

use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Traits\MakeableTrait;
use DigitalCreative\Dashboard\Traits\ResolveFieldsTrait;
use DigitalCreative\Dashboard\Traits\ResolveFiltersTrait;
use DigitalCreative\Dashboard\Traits\ResolveUriKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class AbstractResource
{

    use ResolveFieldsTrait;
    use ResolveFiltersTrait;
    use ResolveUriKey;
    use MakeableTrait;

    private BaseRequest $request;

    public function __construct(BaseRequest $request)
    {
        $this->request = $request;
    }

    public function getDescriptor(): array
    {
        return [
            'name' => $this->label(),
            'label' => Str::plural($this->label()),
            'uriKey' => self::uriKey(),
        ];
    }

    public static function humanize(string $value): string
    {
        return Str::title(Str::snake($value, ' '));
    }

    public function label(): string
    {
        return static::humanize(class_basename(static::class));
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
            'fields' => $this->resolveFieldsUsingModel($model)->jsonSerialize(),
        ];
    }

    public function create(): Collection
    {
        return $this->resolveFields();
    }

    public function store(): void
    {

        $bag = new FieldsData();

        $fields = $this->resolveFields();

        $this->validateFields($fields);

        $request = $this->getRequest();

        $callbacks = $this->filterNonUpdatableFields($fields)
                          ->map(fn(AbstractField $field) => $field->fillUsingRequest($bag, $request));

        $this->repository()->create($bag);

        $callbacks->filter()->each(fn(callable $function) => $function());

    }

    public function update(): bool
    {
        $fields = $this->filterNonUpdatableFields(
            $this->resolveFieldsUsingRequest($this->request)
                 ->filter(function(AbstractField $field) {
                     return $field->isRequired($this->getRequest()) || $field->isDirty();
                 })
        );

        $this->validateFields($fields);

        return $this->repository()->updateResource(
            $this->findResource(), $fields->pluck('value', 'attribute')->toArray()
        );
    }

    private function findResource(): ?Model
    {
        return once(function() {
            return $this->repository()->findByKey(
                $this->request->route('key')
            );
        });
    }

    public function repository(): ResourceRepository
    {
        return new ResourceRepository($this->getModel());
    }

    public function getFiltersListing(): array
    {
        return $this->resolveFilters()->toArray();
    }

    public function index(): array
    {

        $fields = $this->resolveFields($this->request->input('for', 'fields'));

        $request = $this->getRequest();

        $filters = new FilterCollection($this->resolveFilters(), $request->query('filters'));

        $total = $this->repository()->count($filters);

        $resources = $this->repository()
                          ->findCollection($filters, (int) $this->request->query('page', 1))
                          ->map(static function(Model $model) use ($request, $fields) {

                              return [
                                  'key' => $model->getKey(),
                                  'fields' => $fields->map(fn(AbstractField $field) => $field->resolveUsingModel($request, $model)->jsonSerialize()),
                              ];

                          });

        return [
            'total' => $total,
            'resources' => $resources,
        ];

    }

    public function searchBelongsToRelation(): Collection
    {

        $request = $this->getRequest();

        $field = $this->findFieldByAttribute($request->route('field'));

        if ($field instanceof BelongsToField && $field->isSearchable()) {

            $resource = $field->getRelatedResource();
            $repository = new ResourceRepository($resource->getModel());

            $models = $repository->searchForRelatedEntries(
                $field->resolveSearchCallback(), $request
            );

            return $models->map(static function(Model $model) use ($resource) {
                return collect($resource->resolveFieldsUsingModel($model)->jsonSerialize())->pluck('value', 'attribute');
            });

        }

        return abort(404);

    }

}
