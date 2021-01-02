<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature;

use DigitalCreative\Jaqen\Concerns\WithEvents;
use DigitalCreative\Jaqen\Fields\AbstractField;
use DigitalCreative\Jaqen\Fields\EditableField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Jaqen\Tests\TestCase;
use DigitalCreative\Jaqen\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Jaqen\Tests\Traits\RequestTrait;
use DigitalCreative\Jaqen\Tests\Traits\ResourceTrait;
use DigitalCreative\Jaqen\Traits\EventsTrait;
use DigitalCreative\Jaqen\Traits\FieldsEvents;

class FieldEventTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

    public function test_before_create_event_works(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithEvents $field
         */
        [ $resource, $field ] = $this->getPreConfiguredResource();

        $field->beforeCreate(function(array $data) {

            $this->assertEquals([ 'name' => 'original' ], $data);

            return [
                'name' => 'hello world',
                'email' => 'email@email.com',
            ];

        });

        $this->storeResponse($resource, [ 'name' => 'original' ]);

        $this->assertDatabaseHas('users', [
            'name' => 'hello world',
            'email' => 'email@email.com',
        ]);

    }

    public function test_after_create_event_works(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithEvents $field
         */
        [ $resource, $field ] = $this->getPreConfiguredResource();

        $field->afterCreate(function($model) {
            $this->assertInstanceOf(UserModel::class, $model);
        });

        $this->storeResponse($resource);

    }

    public function test_after_update_event_works(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithEvents $field
         */
        [ $resource, $field ] = $this->getPreConfiguredResource();

        $user = UserFactory::new()->create();

        $field->afterUpdate(function($model) use ($user) {
            $this->assertEquals($model->getKey(), $user->getKey());
        });

        $this->updateResponse($resource, $user->id, [ 'name' => 'updated' ]);

    }

    public function test_before_update_event_works(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithEvents $field
         */
        [ $resource, $field ] = $this->getPreConfiguredResource();

        $user = UserFactory::new()->create();

        $field->beforeUpdate(function(UserModel $model, array $data) use ($user) {

            $this->assertEquals([ 'name' => 'updated' ], $data);
            $this->assertEquals($model->getKey(), $user->getKey());

            return [ 'name' => 'modified' ];

        });

        $this->updateResponse($resource, $user->id, [ 'name' => 'updated' ]);

        $this->assertEquals('modified', $user->fresh()->name);

    }

    public function test_update_events_are_not_triggered_if_field_is_not_updated(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithEvents $field
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

        $this->updateResponse($resource, UserFactory::new()->create()->id);

        $this->assertFalse($called);

    }

    public function test_before_and_after_delete_event_works(): void
    {

        /**
         * @var AbstractResource $resource
         * @var WithEvents & AbstractField $field
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

        $this->deleteResponse($resource, $users->pluck('id')->toArray());

        $this->assertEquals(5, $afterDelete);
        $this->assertEquals(5, $beforeDelete);

    }

    private function getPreConfiguredResource(): array
    {

        $field = new class('Name') extends EditableField implements WithEvents {
            use EventsTrait;
        };

        $resource = $this->makeResource()->addDefaultFields($field);

        return [ $resource, $field ];

    }

}
