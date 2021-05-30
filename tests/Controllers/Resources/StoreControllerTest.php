<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers\Resources;

use DigitalCreative\Jaqen\Repository\Repository;
use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\TestCase;
use Mockery\MockInterface;

class StoreControllerTest extends TestCase
{

    public function test_can_create_resources(): void
    {

        $data = [
            'name' => 'demo',
            'email' => 'demo@email.com',
            'gender' => 'male',
            'password' => 123456,
        ];

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             EditableField::make('name'),
                             EditableField::make('email'),
                             EditableField::make('gender'),
                             EditableField::make('password'),
                         );

        $this->resourceCreateApi($resource, $data)
             ->assertCreated()
             ->assertJson($data);

        $this->assertDatabaseHas('users', $data);

    }

    public function test_returning_custom_repository_works_as_expected(): void
    {

        $user = UserFactory::new()->create();

        $repository = $this->mock(Repository::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('create')->andReturn($user);
        });

        $resource = $this->makeResource()
                         ->useRepository($repository);

        $this->resourceCreateApi($resource)
             ->assertCreated()
             ->assertJsonFragment([ 'id' => $user->id ]);

    }

}
