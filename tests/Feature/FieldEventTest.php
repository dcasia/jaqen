<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature;

use DigitalCreative\Dashboard\Concerns\WithFieldEvent;
use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Http\Controllers\DeleteController;
use DigitalCreative\Dashboard\Http\Controllers\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\UpdateController;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;
use DigitalCreative\Dashboard\Traits\FieldsEvents;

class FieldEventTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

    public function test_before_create_event_works(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithFieldEvent $field
         */
        [ $resource, $field ] = $this->getPreConfiguredResource();

        $field->beforeCreate(function(array $data) {

            $this->assertEquals([ 'name' => 'original' ], $data);

            return [
                'name' => 'hello world',
                'email' => 'email@email.com',
            ];

        });

        $request = $this->storeRequest($resource::uriKey(), [ 'name' => 'original' ]);

        (new StoreController())->store($request);

        $this->assertDatabaseHas('users', [
            'name' => 'hello world',
            'email' => 'email@email.com',
        ]);

    }

    public function test_after_create_event_works(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithFieldEvent $field
         */
        [ $resource, $field ] = $this->getPreConfiguredResource();

        $field->afterCreate(function($data) {
            $this->assertTrue($data);
        });

        $request = $this->storeRequest($resource::uriKey());

        (new StoreController())->store($request);

    }

    public function test_after_update_event_works(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithFieldEvent $field
         */
        [ $resource, $field ] = $this->getPreConfiguredResource();

        $user = UserFactory::new()->create();

        $field->afterUpdate(function($model) use ($user) {
            $this->assertEquals($model->getKey(), $user->getKey());
        });

        $request = $this->updateRequest($resource::uriKey(), $user->id, [ 'name' => 'updated' ]);

        (new UpdateController())->update($request);

    }

    public function test_before_update_event_works(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithFieldEvent $field
         */
        [ $resource, $field ] = $this->getPreConfiguredResource();

        $user = UserFactory::new()->create();

        $field->beforeUpdate(function(UserModel $model, array $data) use ($user) {

            $this->assertEquals([ 'name' => 'updated' ], $data);
            $this->assertEquals($model->getKey(), $user->getKey());

            return [ 'name' => 'modified' ];

        });

        $request = $this->updateRequest($resource::uriKey(), $user->id, [ 'name' => 'updated' ]);

        (new UpdateController())->update($request);

        $this->assertEquals('modified', $user->fresh()->name);

    }

    public function test_update_events_are_not_triggered_if_field_is_not_updated(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithFieldEvent $field
         */
        [ $resource, $field ] = $this->getPreConfiguredResource();

        $called = false;

        $field
            ->afterUpdate(function(UserModel $model) use (&$called) {
                $called = true;
            })
            ->beforeUpdate(function(UserModel $model, array $data) use (&$called) {
                $called = true;
            });

        $request = $this->updateRequest($resource::uriKey(), UserFactory::new()->create()->id);

        (new UpdateController())->update($request);

        $this->assertFalse($called);

    }

    public function test_before_and_after_delete_event_works(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithFieldEvent & AbstractField $field
         */
        [ $resource, $field ] = $this->getPreConfiguredResource();

        $users = UserFactory::new()->count(5)->create();

        $beforeDelete = 0;
        $afterDelete = 0;

        $this->assertNull($field->value);

        $field->beforeDelete(function(UserModel $model) use (&$beforeDelete, $field) {
            $this->assertEquals($model->name, $field->value);
            $beforeDelete++;
        });

        $field->afterDelete(function() use (&$afterDelete) {
            $afterDelete++;
        });

        $request = $this->deleteRequest($resource::uriKey(), $users->pluck('id')->toArray());

        (new DeleteController())->delete($request);

        $this->assertEquals(5, $afterDelete);
        $this->assertEquals(5, $beforeDelete);

    }

    private function getPreConfiguredResource(): array
    {

        $field = new class('Name') extends EditableField implements WithFieldEvent {
            use FieldsEvents;
        };

        $resource = $this->makeResource()->addDefaultFields($field);

        return [ $resource, $field ];

    }

}
