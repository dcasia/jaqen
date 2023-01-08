<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers\Resources;

use DigitalCreative\Jaqen\Services\ResourceManager\FilterCollection;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Filters\FilterWithRequiredFields;
use DigitalCreative\Jaqen\Tests\Fixtures\Filters\GenderFilter;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\ResourceWithRequiredFilters;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\UserResource;
use DigitalCreative\Jaqen\Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function test_resource_listing(): void
    {
        UserFactory::new()->create();

        $this->registerResource(UserResource::class);

        $this->resourceIndexApi(UserResource::class)
            ->assertJsonStructure([
                'total',
                'from',
                'to',
                'currentPage',
                'lastPage',
                'resources' => [
                    [
                        'key',
                        'fields' => [
                            [
                                'label',
                                'attribute',
                                'value',
                                'component',
                                'additionalInformation',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_resource_listing_filters(): void
    {
        UserFactory::new()->count(5)->create([ 'gender' => 'male' ]);
        UserFactory::new()->count(5)->create([ 'gender' => 'female' ]);

        $filters = FilterCollection::fake([
            GenderFilter::uriKey() => [ 'gender' => 'male' ],
        ]);

        $this->registerResource(UserResource::class);

        $this->resourceIndexApi(UserResource::class, filters: $filters)
            ->assertOk()
            ->assertJsonCount(5, 'resources')
            ->assertJsonFragment([
                'total' => 5,
            ]);
    }

    public function test_filters_validation_works(): void
    {
        $this->registerResource(ResourceWithRequiredFilters::class);

        $filterUriKey = FilterWithRequiredFields::uriKey();

        $filters = FilterCollection::fake([ $filterUriKey => [ 'name' => '' ] ]);

        $this->resourceIndexApi(ResourceWithRequiredFilters::class, filters: $filters)
            ->assertUnprocessable()
            ->assertJsonFragment([
                'errors' => [
                    $filterUriKey => [
                        'name' => [
                            'The name field is required.',
                        ],
                    ],
                ],
            ]);
    }

    public function test_fields_for_works_correctly_on_resource(): void
    {
        $user = UserFactory::new()->create();
        $this->registerResource(UserResource::class);

        $this->resourceIndexApi(UserResource::class, fieldsFor: 'index')
            ->assertOk()
            ->assertJsonFragment([
                'total' => 1,
                'resources' => [
                    [
                        'key' => 1,
                        'fields' => [
                            [
                                'label' => 'id',
                                'attribute' => 'id',
                                'value' => $user->id,
                                'component' => 'read-only-field',
                                'additionalInformation' => null,
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_ensure_the_results_are_distinct(): void
    {
        $users = UserFactory::new()->count(2)->create();

        $this->registerResource(UserResource::class);

        $this->resourceIndexApi(UserResource::class, fieldsFor: 'index')
            ->assertOk()
            ->assertJsonPath('resources.0.fields.0.value', $users->first()->id)
            ->assertJsonPath('resources.1.fields.0.value', $users->last()->id);
    }

    public function test_pagination(): void
    {
        UserFactory::new()->count(30)->create();

        $this->registerResource(UserResource::class);

        $this->resourceIndexApi(UserResource::class, page: 2)
            ->assertOk()
            ->assertJsonFragment([
                'total' => 30,
                'from' => 16,
                'to' => 30,
                'currentPage' => 2,
                'lastPage' => 2,
            ]);
    }

    public function test_404_is_returned_if_resource_does_not_exist(): void
    {
        $this->resourceIndexApi(UserResource::class)
            ->assertNotFound();
    }
}
