<?php

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\Fixtures\Models\Client;
use DigitalCreative\Dashboard\Tests\TestCase;

class ResourceDetailTest extends TestCase
{

    public function test_resource_detail(): void
    {

        factory(Client::class)->create();

        $response = $this->withExceptionHandling()
                         ->getJson('/dashboard-api/clients/1')
                         ->assertStatus(200);

        $response->assertJsonStructure([
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
        ]);

    }

}
