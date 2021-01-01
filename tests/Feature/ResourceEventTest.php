<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature;

use DigitalCreative\Jaqen\Fields\EditableField;
use DigitalCreative\Jaqen\Resources\AbstractResource;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Jaqen\Tests\TestCase;
use DigitalCreative\Jaqen\Tests\Traits\RequestTrait;
use DigitalCreative\Jaqen\Tests\Traits\ResourceTrait;

class ResourceEventTest extends TestCase
{

    use ResourceTrait;
    use RequestTrait;

    public function test_before_create_event_works(): void
    {

        $resource = $this->getPreConfiguredResource()
                         ->beforeCreate(function(array $data) {
                             $this->assertEquals([ 'name' => 'ignored' ], $data);
                             return [ 'name' => 'hello' ];
                         });

        $this->storeResponse($resource, [ 'name' => 'ignored' ]);

        $this->assertDatabaseHas('users', [ 'name' => 'hello' ]);

    }

    public function test_after_create_event_works(): void
    {

        $resource = $this->getPreConfiguredResource()
                         ->afterCreate(function(UserModel $model) {
                             $this->assertInstanceOf(UserModel::class, $model);
                             return [ 'success' => true ];
                         });

        $response = $this->storeResponse($resource, [ 'name' => 'ignored' ]);

        $this->assertEquals([ 'success' => true ], $response);

    }

    public function test_chaining_multiple_after_create_event_works(): void
    {

        $resource = $this->getPreConfiguredResource()
                         ->afterCreate(function(UserModel $model) {
                             $this->assertInstanceOf(UserModel::class, $model);
                             return [ 'success' => true ];
                         })
                         ->afterCreate(function(array $data) {
                             $this->assertEquals([ 'success' => true ], $data);
                             return array_merge($data, [ 'appended' => true ]);
                         });

        $response = $this->storeResponse($resource, [ 'name' => 'ignored' ]);

        $this->assertEquals([ 'success' => true, 'appended' => true ], $response);

    }

    public function test_before_update_event_works(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->getPreConfiguredResource()
                         ->beforeUpdate(function(UserModel $model, array $data) use ($user) {
                             $this->assertEquals($user->getKey(), $model->getKey());
                             $this->assertSame([ 'name' => 'ignored' ], $data);
                             return [ 'name' => 'hello' ];
                         });

        $this->updateResponse($resource, $user->id, [ 'name' => 'ignored' ]);

        $this->assertDatabaseHas('users', [ 'name' => 'hello' ]);

    }

    public function test_after_update_event_works(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->getPreConfiguredResource()
                         ->afterUpdate(function(UserModel $model) use ($user) {
                             $this->assertEquals($user->getKey(), $model->getKey());
                         });

        $this->updateResponse($resource, $user->id, [ 'name' => 'ignored' ]);

    }

    public function test_before_delete_event_works(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->getPreConfiguredResource()
                         ->beforeDelete(function(UserModel $model) use ($user) {
                             $this->assertEquals($user->getKey(), $model->getKey());
                         });

        $this->deleteResponse($resource, [ $user->id ]);

    }

    public function test_after_delete_event_works(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->getPreConfiguredResource()
                         ->afterDelete(function(UserModel $model) use ($user) {
                             $this->assertEquals($user->getKey(), $model->getKey());
                             $this->assertFalse($model->exists);
                         });

        $this->deleteResponse($resource, [ $user->id ]);

    }

    private function getPreConfiguredResource(): AbstractResource
    {
        return $this->makeResource()->addDefaultFields(new EditableField('Name'));
    }

}
