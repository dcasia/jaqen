<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Http\Controllers\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\UpdateController;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\User as UserResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class FieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

    public function test_field_validation_on_create_works(): void
    {

        $this->expectException(ValidationException::class);

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             (new EditableField('name'))->rulesForCreate('required')
                         );

        $request = $this->storeRequest($resource::uriKey(), [ 'name' => null ]);

        (new StoreController)->store($request);

    }

    public function test_field_validation_on_update_works(): void
    {

        /**
         * @var UserModel $user
         */
        $user = factory(UserModel::class)->create();

        $this->expectException(ValidationException::class);

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             (new EditableField('name'))->rulesForUpdate('required')
                         );

        $request = $this->updateRequest($resource::uriKey(), $user->id, [ 'name' => null ]);

        (new UpdateController())->update($request);

    }

    public function test_fields_are_validate_even_if_they_are_not_sent_on_the_request(): void
    {

        $this->expectException(ValidationException::class);

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             (new EditableField('name'))->rulesForCreate('required')
                         );

        $request = $this->storeRequest($resource::uriKey());

        (new StoreController)->store($request);

    }

    public function test_rules_for_update_works_when_value_is_not_sent(): void
    {

        /**
         * @var UserModel $user
         */
        $user = factory(UserModel::class)->create();

        $this->expectException(ValidationException::class);

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             (new EditableField('name'))->rulesForUpdate('required'),
                             (new EditableField('email'))->rulesForUpdate('required')
                         );

        $request = $this->updateRequest($resource::uriKey(), $user->id, [ 'name' => 'Test' ]);

        (new UpdateController())->update($request);

    }

    public function test_custom_fields_name_gets_resolved_correctly(): void
    {

        $resource = $this->makeResource()
                         ->fieldsFor('index-listing', function() {
                             return [
                                 new EditableField('name'),
                             ];
                         });

        $request = $this->createRequest($resource::uriKey(), [ 'fieldsFor' => 'index-listing' ]);

        $this->assertEquals([
            [
                'label' => 'name',
                'attribute' => 'name',
                'value' => null,
                'component' => 'editable-field',
                'additionalInformation' => null,
            ],
        ], $resource->resolveFields($request)->toArray());

    }

    public function test_fields_is_resolved_from_method_if_exists(): void
    {

        $request = $this->createRequest(UserResource::uriKey(), [ 'fieldsFor' => 'demo' ]);

        $resource = new class($request) extends AbstractResource {

            public function getModel(): Model
            {
                return new UserModel();
            }

            public function fieldsForDemo(): array
            {
                return [
                    new EditableField('name'),
                ];
            }
        };

        $response = $resource->resolveFields($request);

        $this->assertEquals([
            [
                'label' => 'name',
                'attribute' => 'name',
                'value' => null,
                'component' => 'editable-field',
                'additionalInformation' => null,
            ],
        ], $response->toArray());

    }

    public function test_field_can_resolve_default_value(): void
    {

        $request = $this->createRequest(UserResource::uriKey());

        $response = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             EditableField::make('Name')->default('Demo'),
                             EditableField::make('Email')->default(fn() => 'demo@email.com'),
                         )
                         ->resolveFields($request);

        $this->assertEquals(
            [ 'name' => 'Demo', 'email' => 'demo@email.com' ],
            $response->pluck('value', 'attribute')->toArray()
        );

    }

    public function test_it_returns_only_the_specified_fields(): void
    {

        $request = $this->createRequest(UserResource::uriKey(), [ 'only' => 'first_name,last_name' ]);

        $response = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             EditableField::make('First Name')->default('Hello'),
                             EditableField::make('Last Name')->default('World'),
                             EditableField::make('Email')->default('demo@email.com'),
                         )
                         ->resolveFields($request);

        $this->assertEquals(
            [ 'first_name' => 'Hello', 'last_name' => 'World' ],
            $response->pluck('value', 'attribute')->toArray()
        );

    }

    public function test_only_fields_remove_empty_spaces_correctly(): void
    {

        /**
         * Space between , will be trimmed
         */
        $request = $this->createRequest(UserResource::uriKey(), [ 'only' => 'first_name , email' ]);

        $response = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             EditableField::make('First Name')->default('Hello'),
                             EditableField::make('Last Name')->default('World'),
                             EditableField::make('Email')->default('demo@email.com'),
                         )
                         ->resolveFields($request);

        $this->assertEquals(
            [ 'first_name' => 'Hello', 'email' => 'demo@email.com' ],
            $response->pluck('value', 'attribute')->toArray()
        );

    }

    public function test_expect_fields_filter_works_correctly(): void
    {

        $request = $this->createRequest(UserResource::uriKey(), [ 'except' => 'first_name , last_name' ]);

        $response = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             EditableField::make('First Name')->default('Hello'),
                             EditableField::make('Last Name')->default('World'),
                             EditableField::make('Email')->default('demo@email.com'),
                         )
                         ->resolveFields($request);

        $this->assertEquals(
            [ 'email' => 'demo@email.com' ],
            $response->pluck('value', 'attribute')->toArray()
        );

    }

    public function test_field_attribute_is_correctly_casted_to_lowercase(): void
    {
        $this->assertEquals('id', EditableField::make('ID')->attribute);
        $this->assertEquals('test_id', EditableField::make('Test Id')->attribute);
        $this->assertEquals('test_id', EditableField::make('TEST Id')->attribute);
        $this->assertEquals('test_id', EditableField::make(' test   Id ')->attribute);
        $this->assertEquals('test_1', EditableField::make('Test 1')->attribute);
        $this->assertEquals('1_test_1', EditableField::make('1 Test 1')->attribute);
        $this->assertEquals('2_hello_world_2', EditableField::make(' 2 Hello  worlD 2 ')->attribute);
        $this->assertEquals('helloworld', EditableField::make('HelloWorld')->attribute);
        $this->assertEquals('hello_world', EditableField::make('HelloWorld', 'hello_world')->attribute);
    }

}
