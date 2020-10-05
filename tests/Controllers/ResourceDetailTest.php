<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
use DigitalCreative\Dashboard\Tests\TestCase;

class ResourceDetailTest extends TestCase
{

    public function test_resource_detail(): void
    {

        UserFactory::new()->create();

        $response = $this->getJson('/dashboard-api/users/1')
                         ->assertStatus(200);

        $response->assertJsonStructure([
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
