<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature;

use DigitalCreative\Dashboard\AbstractResource;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\StoreResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\User as UserResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class FieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

    public function test_field_validation_on_create_works(): void
    {

        $request = $this->makeRequest('/', 'POST', [ 'name' => null ], StoreResourceRequest::class);

        $this->expectException(ValidationException::class);

        $this->getResource($request)
             ->addDefaultFields(
                 (new EditableField('name'))->rulesForCreate('required')
             )
             ->store();

    }

    public function test_field_validation_on_update_works(): void
    {

        $request = $this->makeRequest('/', 'POST', [ 'name' => null ], UpdateResourceRequest::class);

        $this->expectException(ValidationException::class);

        $this->getResource($request)
             ->addDefaultFields(
                 (new EditableField('name'))->rulesForUpdate('required')
             )
             ->update();

    }

    public function test_fields_are_validate_even_if_they_are_not_sent_on_the_request(): void
    {

        $request = $this->makeRequest('/', 'POST', [], StoreResourceRequest::class);

        $this->expectException(ValidationException::class);

        $this->getResource($request)
             ->addDefaultFields(
                 (new EditableField('name'))->rulesForCreate('required')
             )
             ->store();

    }

    public function test_rules_for_update_works_when_value_is_not_sent(): void
    {

        $request = $this->makeRequest('/', 'POST', [ 'name' => 'Test' ], UpdateResourceRequest::class);

        $this->expectException(ValidationException::class);

        $this->getResource($request)
             ->addDefaultFields(
                 (new EditableField('name'))->rulesForUpdate('required'),
                 (new EditableField('email'))->rulesForUpdate('required')
             )
             ->update();

    }

    public function test_custom_fields_name_gets_resolved_correctly(): void
    {

        $request = $this->createRequest(UserResource::uriKey(), [ 'fieldsFor' => 'index-listing' ]);

        $response = $this->getResource($request)
                         ->fieldsFor('index-listing', function() {
                             return [
                                 new EditableField('name'),
                             ];
                         })
                         ->create();

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

    public function test_fields_is_resolved_from_method_if_exists(): void
    {

        $request = $this->createRequest(UserResource::uriKey(), [ 'fieldsFor' => 'demo' ]);

        $resource = new class($request) extends AbstractResource {
            public function fieldsForDemo(): array
            {
                return [
                    new EditableField('name'),
                ];
            }
        };

        $response = $resource->create();

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

        $response = $this->makeResource($request, UserModel::class)
                         ->addDefaultFields(
                             EditableField::make('Name')->default('Demo'),
                             EditableField::make('Email')->default(fn() => 'demo@email.com'),
                         )
                         ->create();

        $this->assertEquals(
            [ 'name' => 'Demo', 'email' => 'demo@email.com' ],
            $response->pluck('value', 'attribute')->toArray()
        );

    }

    public function test_it_returns_only_the_specified_fields(): void
    {

        $request = $this->createRequest(UserResource::uriKey(), [ 'only' => 'first_name,last_name' ]);

        $response = $this->makeResource($request, UserModel::class)
                         ->addDefaultFields(
                             EditableField::make('First Name')->default('Hello'),
                             EditableField::make('Last Name')->default('World'),
                             EditableField::make('Email')->default('demo@email.com'),
                         )
                         ->create();

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

        $response = $this->makeResource($request, UserModel::class)
                         ->addDefaultFields(
                             EditableField::make('First Name')->default('Hello'),
                             EditableField::make('Last Name')->default('World'),
                             EditableField::make('Email')->default('demo@email.com'),
                         )
                         ->create();

        $this->assertEquals(
            [ 'first_name' => 'Hello', 'email' => 'demo@email.com' ],
            $response->pluck('value', 'attribute')->toArray()
        );

    }

    public function test_expect_fields_filter_works_correctly(): void
    {

        $request = $this->createRequest(UserResource::uriKey(), [ 'except' => 'first_name , last_name' ]);

        $response = $this->makeResource($request, UserModel::class)
                         ->addDefaultFields(
                             EditableField::make('First Name')->default('Hello'),
                             EditableField::make('Last Name')->default('World'),
                             EditableField::make('Email')->default('demo@email.com'),
                         )
                         ->create();

        $this->assertEquals(
            [ 'email' => 'demo@email.com' ],
            $response->pluck('value', 'attribute')->toArray()
        );

    }

    private function getResource(BaseRequest $request): AbstractResource
    {
        return new class($request) extends AbstractResource {
            public static string $model = UserModel::class;
        };
    }

}
