<?php

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\FilterCollection;
use DigitalCreative\Dashboard\Tests\Fixtures\Filters\GenderFilter;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Client;
use DigitalCreative\Dashboard\Tests\TestCase;


class ResourceIndexTest extends TestCase
{

    public function test_resource_listing(): void
    {

        factory(Client::class)->create();

        $response = $this->withExceptionHandling()
                         ->getJson('/dashboard-api/clients')
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
                            'additionalInformation'
                        ]
                    ]
                ]
            ],
        ]);

    }

    public function test_resource_listing_filters(): void
    {

        factory(Client::class, 5)->create([ 'gender' => 'male' ]);
        factory(Client::class, 5)->create([ 'gender' => 'female' ]);

        $filters = FilterCollection::test([
            GenderFilter::uriKey() => [
                'gender' => 'male'
            ]
        ]);

        $this->withExceptionHandling()
             ->getJson('/dashboard-api/clients?filters=' . $filters)
             ->assertStatus(200)
             ->assertJsonCount(5, 'resources')
             ->assertJsonFragment([
                 'total' => 5
             ]);

    }

}
