<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers\Resources;

use DigitalCreative\Jaqen\Fields\EditableField;
use DigitalCreative\Jaqen\Repository\Repository;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Jaqen\Tests\TestCase;
use DigitalCreative\Jaqen\Tests\Traits\RequestTrait;
use DigitalCreative\Jaqen\Tests\Traits\ResourceTrait;
use Mockery\MockInterface;

class StoreControllerTest extends TestCase
{

    use ResourceTrait;
    use RequestTrait;

    public function test_can_create_resources(): void
    {

        $data = [
            'name' => 'demo',
            'email' => 'demo@email.com',
            'gender' => 'male',
            'password' => 123456,
        ];

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             EditableField::make('name'),
                             EditableField::make('email'),
                             EditableField::make('gender'),
                             EditableField::make('password'),
                         );

        $this->callStore($resource, $data)->assertStatus(201);

        $this->assertDatabaseHas('users', $data);

    }

    public function test_returning_a_custom_data_works_as_expected(): void
    {

        $user = UserFactory::new()->create();

        $repository = $this->mock(Repository::class, function(MockInterface $mock) use ($user) {
            $mock->shouldReceive('create')->andReturn($user);
        });

        $resource = $this->makeResource(UserModel::class)
                         ->useRepository($repository);

        $this->callStore($resource)
             ->assertStatus(201)
             ->assertJsonFragment([ 'id' => $user->id ]);

    }

}
