<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers\Resources;

use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\UserResource;
use DigitalCreative\Jaqen\Tests\TestCase;

class DetailControllerTest extends TestCase
{
    public function test_resource_detail(): void
    {
        $user = UserFactory::new()->create();

        $this->registerResource(UserResource::class);

        $this->resourceShowApi(UserResource::class, key: $user->id)
            ->assertOk()
            ->assertJsonStructure([
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
            ]);
    }
}
