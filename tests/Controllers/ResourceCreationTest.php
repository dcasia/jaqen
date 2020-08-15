<?php

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\TestCase;


class ResourceCreationTest extends TestCase
{

    public function test_can_create_resources(): void
    {

        $data = [
            'name' => 'demo',
            'email' => 'demo@email.com',
            'gender' => 'male'
        ];

        $this->withExceptionHandling()
             ->postJson('/dashboard-api/create/clients', $data)
             ->assertStatus(200);

        $this->assertDatabaseHas('clients', $data);

    }

}
