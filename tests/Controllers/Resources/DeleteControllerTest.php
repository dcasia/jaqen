<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers\Resources;

use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\UserResource;
use DigitalCreative\Jaqen\Tests\TestCase;

class DeleteControllerTest extends TestCase
{
    public function test_resource_delete(): void
    {
        $data = [
            'name' => 'Demo',
            'email' => 'email@email.com',
        ];

        $user = UserFactory::new()->create($data);

        $this->registerResource(UserResource::class);
        $this->resourceDestroyApi(UserResource::class, ids: [ $user->id ])
            ->assertNoContent();

        $this->assertDatabaseMissing(User::class, $data);
    }

    public function test_deleting_multiple_items_works(): void
    {
        $users = UserFactory::new()->count(3)->create();

        UserFactory::new()->create();

        $this->registerResource(UserResource::class);
        $this->resourceDestroyApi(UserResource::class, ids: $users->pluck('id')->toArray())
            ->assertNoContent();

        $this->assertDatabaseCount(User::class, 1);
    }
}
