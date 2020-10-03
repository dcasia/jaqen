<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;

class ResourceListTest extends TestCase
{

    public function test_resource_list_api(): void
    {

        factory(UserModel::class)->create();

        $response = $this->getJson('/dashboard-api/resources')
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
