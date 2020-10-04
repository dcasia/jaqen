<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;
use Illuminate\Support\Collection;

class DeleteControllerTest extends TestCase
{

    public function test_resource_delete(): void
    {
        $data = [
            'name' => 'Demo',
            'email' => 'email@email.com',
        ];

        factory(UserModel::class)->create($data);

        $this->deleteJson('/dashboard-api/users', [ 'ids' => [ 1 ] ])
             ->assertStatus(204);

        $this->assertDatabaseMissing('users', $data);
    }

    public function test_deleting_multiple_items_works(): void
    {
        /**
         * @var Collection $users
         */
        $users = factory(UserModel::class, 3)->create();

        factory(UserModel::class)->create();

        $this->deleteJson('/dashboard-api/users', [ 'ids' => $users->pluck('id') ])
             ->assertStatus(204);

        $this->assertDatabaseCount('users', 1);
    }

}
