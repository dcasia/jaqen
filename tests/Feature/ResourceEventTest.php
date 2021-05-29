<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature;

use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Jaqen\Tests\TestCase;

class ResourceEventTest extends TestCase
{

    public function test_before_create_event_works(): void
    {

        $resource = $this->getPreConfiguredResource()
                         ->beforeCreate(function (array $data) {
                             $this->assertEquals([ 'name' => 'ignored' ], $data);

                             return [ 'name' => 'hello' ];
                         });

        $this->resourceStoreApi($resource, [ 'name' => 'ignored' ])
             ->assertCreated();

        $this->assertDatabaseHas('users', [ 'name' => 'hello' ]);

    }

    public function test_after_create_event_works(): void
    {

        $resource = $this->getPreConfiguredResource()
                         ->afterCreate(function (UserModel $model) {
                             $this->assertInstanceOf(UserModel::class, $model);

                             return [ 'success' => true ];
                         });

        $this->resourceStoreApi($resource, [ 'name' => 'ignored' ])
             ->assertCreated()
             ->assertJson([ 'success' => true ]);

    }

    public function test_chaining_multiple_after_create_event_works(): void
    {

        $resource = $this->getPreConfiguredResource()
                         ->afterCreate(function (UserModel $model) {
                             $this->assertInstanceOf(UserModel::class, $model);

                             return [ 'success' => true ];
                         })
                         ->afterCreate(function (array $data) {
                             $this->assertEquals([ 'success' => true ], $data);

                             return array_merge($data, [ 'appended' => true ]);
                         });

        $this->resourceStoreApi($resource, [ 'name' => 'ignored' ])
             ->assertCreated()
             ->assertJson([ 'success' => true, 'appended' => true ]);

    }

    public function test_before_update_event_works(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->getPreConfiguredResource()
                         ->beforeUpdate(function (UserModel $model, array $data) use ($user) {
                             $this->assertEquals($user->getKey(), $model->getKey());
                             $this->assertSame([ 'name' => 'ignored' ], $data);

                             return [ 'name' => 'hello' ];
                         });

        $this->resourceUpdateApi($resource, $user->id, [ 'name' => 'ignored' ])
             ->assertOk();

        $this->assertDatabaseHas('users', [ 'name' => 'hello' ]);

    }

    public function test_after_update_event_works(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->getPreConfiguredResource()
                         ->afterUpdate(function (UserModel $model) use ($user) {
                             $this->assertEquals($user->getKey(), $model->getKey());
                         });

        $this->resourceUpdateApi($resource, $user->id, [ 'name' => 'ignored' ])
             ->assertOk();

    }

    public function test_before_delete_event_works(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->getPreConfiguredResource()
                         ->beforeDelete(function (UserModel $model) use ($user) {
                             $this->assertEquals($user->getKey(), $model->getKey());
                         });

        $this->resourceDestroyApi($resource, [ $user->id ])
             ->assertNoContent();

    }

    public function test_after_delete_event_works(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->getPreConfiguredResource()
                         ->afterDelete(function (UserModel $model) use ($user) {
                             $this->assertEquals($user->getKey(), $model->getKey());
                             $this->assertFalse($model->exists);
                         });

        $this->resourceDestroyApi($resource, [ $user->id ])
             ->assertNoContent();

    }

    private function getPreConfiguredResource(): AbstractResource
    {
        return $this->makeResource()->addDefaultFields(EditableField::make('Name'));
    }

}
