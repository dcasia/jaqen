<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;

class ResourceDeleteTest extends TestCase
{

    public function test_resource_delete(): void
    {

        $data = [
            'name' => 'Demo',
            'email' => 'email@email.com',
        ];

        factory(UserModel::class)->create($data);

        $this->delete('/dashboard-api/users/1')->assertStatus(200);

        $this->assertDatabaseMissing('users', $data);

    }

}
