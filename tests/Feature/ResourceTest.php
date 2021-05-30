<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature;

use DigitalCreative\Jaqen\Concerns\WithCustomStore;
use DigitalCreative\Jaqen\Concerns\WithCustomUpdate;
use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\StoreResourceRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\UpdateResourceRequest;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Fields\Invokable\SampleInvokable;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Jaqen\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

class ResourceTest extends TestCase
{

    public function test_invokable_fields_works(): void
    {

        $fields = $this->makeResource()
                       ->fieldsFor('demo', fn() => SampleInvokable::class)
                       ->resolveFields($this->blankRequest([], [ 'fieldsFor' => 'demo' ]));

        $this->assertInstanceOf(EditableField::class, $fields->first());

    }

    public function test_custom_store_works(): void
    {
        $resource = new class($this) extends AbstractResource implements WithCustomStore {

            private TestCase $runner;

            public function __construct(TestCase $runner)
            {
                $this->runner = $runner;
            }

            public function newModel(): Model
            {
                return new UserModel();
            }

            public function storeResource(array $data, StoreResourceRequest $request): array
            {
                $this->runner->assertEquals([ 'name' => 'test' ], $data);

                return [ 'test' => 123 ];
            }

        };

        $resource->addDefaultFields(EditableField::make('Name'));

        $this->registerResource($resource);

        $this->resourceStoreApi($resource, [ 'name' => 'test' ])
             ->assertCreated()
             ->assertJson([ 'test' => 123 ]);
    }

    public function test_custom_update_works(): void
    {
        $resource = new class($this) extends AbstractResource implements WithCustomUpdate {

            private TestCase $runner;

            public function __construct(TestCase $runner)
            {
                $this->runner = $runner;
            }

            public function newModel(): Model
            {
                return new UserModel();
            }

            public function updateResource(Model $model, array $data, UpdateResourceRequest $request): bool
            {
                $this->runner->assertEquals([ 'name' => 'test' ], $data);
                $this->runner->assertEquals($model->getKey(), 6);

                return true;
            }

        };

        $resource->addDefaultFields(EditableField::make('Name'));

        $this->registerResource($resource);

        UserFactory::new()->count(5)->create();

        $user = UserFactory::new()->create();

        $this->resourceUpdateApi($resource, $user->getKey(), [ 'name' => 'test' ])
             ->assertOk();

    }

}
