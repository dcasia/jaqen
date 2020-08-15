<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\TestCase;

class ResourceCreationTest extends TestCase
{

    public function test_can_create_resources(): void
    {

        $data = [
            'name' => 'demo',
            'email' => 'demo@email.com',
            'gender' => 'male',
            'password' => 123456
        ];

        $this->postJson('/dashboard-api/create/users', $data)
             ->assertStatus(200);

        $this->assertDatabaseHas('users', $data);

    }

}
