<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;

class ResourceUpdateTest extends TestCase
{

    public function test_resource_update(): void
    {

        factory(UserModel::class)->create();

        $data = [
            'name' => 'Demo',
            'email' => 'email@email.com'
        ];

        $this->postJson('/dashboard-api/users/1', $data)
             ->assertStatus(200);

        $this->assertDatabaseHas('users', $data);

    }

    public function test_read_only_fields_does_not_get_update(): void
    {

        /**
         * @var UserModel $user
         */
        $user = factory(UserModel::class)->create();

        $this->postJson('/dashboard-api/users/1', [ 'id' => 2 ])
             ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => 1,
            'name' => $user->name,
            'email' => $user->email,
        ]);

    }

}
