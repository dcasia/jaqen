<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Traits;

use DigitalCreative\Jaqen\Fields\Relationships\BelongsToField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use Illuminate\Testing\TestResponse;

trait ApiTrait
{
    public function resourceIndexApi(
        AbstractResource|string $resource,
        string $filters = null,
        string $fieldsFor = null,
        int $page = null,
    ): TestResponse
    {
        return $this->getJson(
            route('jaqen.resource.index', array_filter([
                'resource' => $resource::uriKey(),
                'filters' => $filters,
                'fieldsFor' => $fieldsFor,
                'page' => $page,
            ])),
        );
    }

    public function resourceStoreApi(AbstractResource|string $resource, array $data = []): TestResponse
    {
        return $this->postJson(
            route('jaqen.resource.store', [ 'resource' => $resource::uriKey() ]), $data,
        );
    }

    public function resourceDestroyApi(AbstractResource|string $resource, array $ids = null): TestResponse
    {
        return $this->deleteJson(
            route('jaqen.resource.destroy', [ 'resource' => $resource::uriKey() ]),
            array_filter([ 'ids' => $ids ]),
        );
    }

    public function resourceShowApi(AbstractResource|string $resource, int|string $key): TestResponse
    {
        return $this->get(
            route('jaqen.resource.show', [ 'resource' => $resource::uriKey(), 'key' => $key ]),
        );
    }

    public function resourceUpdateApi(AbstractResource|string $resource, int|string $key, array $data = []): TestResponse
    {
        return $this->patchJson(
            route('jaqen.resource.show', [ 'resource' => $resource::uriKey(), 'key' => $key ]),
            $data,
        );
    }

    public function resourceFieldsApi(AbstractResource|string $resource, string $fieldsFor = null): TestResponse
    {
        return $this->getJson(
            route('jaqen.resource.fields', array_filter([
                'resource' => $resource::uriKey(),
                'fieldsFor' => $fieldsFor,
            ])),
        );
    }

    public function resourceFiltersApi(AbstractResource|string $resource): TestResponse
    {
        return $this->getJson(
            route('jaqen.resource.filters', array_filter([
                'resource' => $resource::uriKey(),
            ])),
        );
    }

    public function resourcesApi(): TestResponse
    {
        return $this->getJson(route('jaqen.resources'));
    }

    public function belongsToSearchApi(AbstractResource|string $resource, BelongsToField $field, array $data): TestResponse
    {
        return $this->getJson(
            route('jaqen.fields.belongs-to', [ 'resource' => $resource::uriKey(), 'field' => $field->getRelationAttribute() ] + $data),
        );
    }
}

