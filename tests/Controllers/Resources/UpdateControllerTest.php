<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers\Resources;

use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\TestCase;

class UpdateControllerTest extends TestCase
{

    public function test_resource_update(): void
    {

        UserFactory::new()->create();

        $data = [
            'name' => 'Demo',
            'email' => 'email@email.com',
        ];

        $this->patchJson('/jaqen-api/crud/users/1', $data)
             ->assertStatus(200);

        $this->assertDatabaseHas('users', $data);

    }

    public function test_read_only_fields_does_not_get_update(): void
    {

        $user = UserFactory::new()->create();

        $this->patchJson('/jaqen-api/crud/users/1', [ 'id' => 2 ])
             ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => 1,
            'name' => $user->name,
            'email' => $user->email,
        ]);

    }

}
