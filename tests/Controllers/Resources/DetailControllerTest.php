<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers\Resources;

use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\TestCase;

class DetailControllerTest extends TestCase
{

    public function test_resource_detail(): void
    {

        UserFactory::new()->create();

        $response = $this->getJson('/jaqen-api/users/1')
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
