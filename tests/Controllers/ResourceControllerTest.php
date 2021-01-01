<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers;

use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\TestCase;

class ResourceControllerTest extends TestCase
{

    public function test_resource_list_api(): void
    {

        UserFactory::new()->create();

        $response = $this->getJson('/jaqen-api/resources')
                         ->assertStatus(200);

        $response->assertJsonStructure([
            [
                'name',
                'label',
                'uriKey',
            ],
        ]);

    }

}
