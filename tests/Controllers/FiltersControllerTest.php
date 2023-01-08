<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers;

use DigitalCreative\Jaqen\Tests\Fixtures\Resources\UserResource;
use DigitalCreative\Jaqen\Tests\TestCase;

class FiltersControllerTest extends TestCase
{
    public function test_resource_filters_works(): void
    {
        $this->registerResource(UserResource::class);

        $this->resourceFiltersApi(UserResource::class)
            ->assertOk()
            ->assertJsonStructure([
                [
                    'uriKey',
                    'fields' => [
                        [
                            'label',
                            'attribute',
                            'value',
                            'component',
                            'additionalInformation' => [
                                'male',
                                'female',
                            ],
                        ],
                    ],
                ],
            ]);
    }
}
