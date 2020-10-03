<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\FilterCollection;
use DigitalCreative\Dashboard\Tests\Fixtures\Filters\FilterWithRequiredFields;
use DigitalCreative\Dashboard\Tests\Fixtures\Filters\GenderFilter;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\ResourceWithRequiredFilters;
use DigitalCreative\Dashboard\Tests\TestCase;

class ResourceIndexTest extends TestCase
{

    public function test_resource_listing(): void
    {

        factory(UserModel::class)->create();

        $response = $this->getJson('/dashboard-api/users')
                         ->assertStatus(200);

        $response->assertJsonStructure([
            'total',
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

        factory(UserModel::class, 5)->create([ 'gender' => 'male' ]);
        factory(UserModel::class, 5)->create([ 'gender' => 'female' ]);

        $filters = FilterCollection::test([
            GenderFilter::uriKey() => [
                'gender' => 'male',
            ],
        ]);

        $this->getJson('/dashboard-api/users?filters=' . $filters)
             ->assertStatus(200)
             ->assertJsonCount(5, 'resources')
             ->assertJsonFragment([
                 'total' => 5,
             ]);

    }

    public function test_filters_validation_works(): void
    {

        $resourceUriKey = ResourceWithRequiredFilters::uriKey();
        $filterUriKey = FilterWithRequiredFields::uriKey();

        $filters = FilterCollection::test([
            $filterUriKey => [
                'name' => '',
            ],
        ]);

        $this->getJson("/dashboard-api/$resourceUriKey?filters=$filters")
             ->assertStatus(422)
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

        $user = factory(UserModel::class)->create();

        $response = $this->getJson('/dashboard-api/users?fieldsFor=index')
                         ->assertStatus(200);

        $response->assertJsonFragment([
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

        $users = factory(UserModel::class, 2)->create();

        $this->getJson('/dashboard-api/users?fieldsFor=index')
             ->assertStatus(200)
             ->assertJsonPath('resources.0.fields.0.value', $users->first()->id)
             ->assertJsonPath('resources.1.fields.0.value', $users->last()->id);

    }

}
