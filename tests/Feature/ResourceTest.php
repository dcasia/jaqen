<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature;

use DigitalCreative\Dashboard\Concerns\WithCustomStore;
use DigitalCreative\Dashboard\Concerns\WithCustomUpdate;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Http\Controllers\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\UpdateController;
use DigitalCreative\Dashboard\Http\Requests\StoreResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;
use Illuminate\Database\Eloquent\Model;

class ResourceTest extends TestCase
{

    use ResourceTrait;
    use RequestTrait;

    public function test_invokable_fields_works(): void
    {

        $invokable = new class {

            public function __invoke()
            {
                return [
                    new EditableField('Test'),
                ];
            }

        };

        $fields = $this->makeResource()
                       ->fieldsFor('demo', fn() => [ $invokable ])
                       ->resolveFields($this->blankRequest([], [ 'fieldsFor' => 'demo' ]));

        $this->assertEquals($fields->first(), $invokable);

    }

    public function test_custom_store_works(): void
    {
        $resource = new class($this) extends AbstractResource implements WithCustomStore {

            private TestCase $runner;

            public function __construct(TestCase $runner)
            {
                $this->runner = $runner;
            }

            public function getModel(): Model
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

        $request = $this->storeRequest($resource::uriKey(), [ 'name' => 'test' ]);

        $response = (new StoreController())->store($request);

        $this->assertEquals([ 'test' => 123 ], $response->getData(true));
    }

    public function test_custom_update_works(): void
    {
        $resource = new class($this) extends AbstractResource implements WithCustomUpdate {

            private TestCase $runner;

            public function __construct(TestCase $runner)
            {
                $this->runner = $runner;
            }

            public function getModel(): Model
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

        $request = $this->updateRequest($resource::uriKey(), $user->getKey(), [ 'name' => 'test' ]);

        $response = (new UpdateController())->update($request);

        $this->assertTrue($response);
    }

}
