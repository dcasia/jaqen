<?php

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\Fixtures\Models\Client as ClientModel;
use DigitalCreative\Dashboard\Tests\TestCase;

class ResourceUpdateTest extends TestCase
{

    public function test_resource_update(): void
    {

        factory(ClientModel::class)->create();

        $data = [
            'name' => 'Demo',
            'email' => 'email@email.com'
        ];

        $this->postJson('/dashboard-api/clients/1', $data)
             ->assertStatus(200);

        $this->assertDatabaseHas('clients', $data);

    }

    public function test_read_only_fields_does_not_get_update(): void
    {

        /**
         * @var ClientModel $client
         */
        $client = factory(ClientModel::class)->create();

        $this->postJson('/dashboard-api/clients/1', [ 'id' => 2 ])
             ->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => 1,
            'name' => $client->name,
            'email' => $client->email,
        ]);

    }

}
