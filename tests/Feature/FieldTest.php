<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature;

use DigitalCreative\Jaqen\Services\Fields\Fields\AbstractField;
use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Jaqen\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class FieldTest extends TestCase
{

    public function test_field_validation_on_create_works(): void
    {

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             EditableField::make('Name')->rulesForCreate('required')
                         );

        $this->resourceCreateApi($resource, [ 'name' => null ])
             ->assertCreated();

    }

    public function test_field_validation_on_update_works(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             EditableField::make('name')->rulesForUpdate('required')
                         );

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $this->resourceUpdateApi($resource, key: $user->id, data: [ 'name' => null ]);

    }

    public function test_fields_are_validate_even_if_they_are_not_sent_on_the_request(): void
    {

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             EditableField::make('Name')->rulesForCreate('required')
                         );

        $this->resourceCreateApi($resource);

    }

    public function test_rules_for_update_works_when_value_is_not_sent(): void
    {

        $user = UserFactory::new()->create();

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             EditableField::make('Name')->rulesForUpdate('required'),
                             EditableField::make('email')->rulesForUpdate('required')
                         );

        $this->resourceUpdateApi($resource, $user->id, [ 'name' => 'Test' ]);

    }

    public function test_custom_fields_name_gets_resolved_correctly(): void
    {

        $resource = $this->makeResource()
                         ->fieldsFor('index-listing', function () {
                             return [
                                 EditableField::make('Name'),
                             ];
                         });

        $this->resourceFieldsApi($resource, fieldsFor: 'index-listing')
             ->assertJson([
                 [
                     'label' => 'Name',
                     'attribute' => 'name',
                     'value' => null,
                     'component' => 'editable-field',
                     'additionalInformation' => null,
                 ],
             ]);

    }

    public function test_fields_is_resolved_from_method_if_exists(): void
    {

        $resource = new class extends AbstractResource {

            public function newModel(): Model
            {
                return new UserModel();
            }

            public function fieldsForDemo(): array
            {
                return [
                    EditableField::make('Name'),
                ];
            }

        };

        $request = $this->fieldsRequest($resource, [ 'fieldsFor' => 'demo' ]);
        $response = $resource->resolveFields($request)->toArray();

        $this->assertEquals([
            [
                'label' => 'Name',
                'attribute' => 'name',
                'value' => null,
                'component' => 'editable-field',
                'additionalInformation' => null,
            ],
        ], $response);

    }

    public function test_field_can_resolve_default_value(): void
    {

        $resource = $this->makeResource();

        $request = $this->fieldsRequest($resource);

        $response = $resource
            ->addDefaultFields(
                EditableField::make('Name')->default('Demo'),
                EditableField::make('Email')->default(fn() => 'demo@email.com'),
            )
            ->resolveFields($request)
            ->each(fn(AbstractField $field) => $field->resolveValueFromRequest($request));

        $this->assertEquals(
            [ 'name' => 'Demo', 'email' => 'demo@email.com' ],
            $response->pluck('value', 'attribute')->toArray()
        );

    }

    public function test_it_returns_only_the_specified_fields(): void
    {

        $resource = $this->makeResource();

        $request = $this->fieldsRequest($resource, [ 'only' => 'first_name,last_name' ]);

        $response = $resource
            ->addDefaultFields(
                EditableField::make('First Name')->default('Hello'),
                EditableField::make('Last Name')->default('World'),
                EditableField::make('Email')->default('demo@email.com'),
            )
            ->resolveFields($request)
            ->each(fn(AbstractField $field) => $field->resolveValueFromRequest($request));

        $this->assertEquals(
            [ 'first_name' => 'Hello', 'last_name' => 'World' ],
            $response->pluck('value', 'attribute')->toArray()
        );

    }

    public function test_only_fields_remove_empty_spaces_correctly(): void
    {

        $resource = $this->makeResource();

        /**
         * Space between , will be trimmed
         */
        $request = $this->fieldsRequest($resource, [ 'only' => 'first_name , email' ]);

        $response = $resource
            ->addDefaultFields(
                EditableField::make('First Name')->default('Hello'),
                EditableField::make('Last Name')->default('World'),
                EditableField::make('Email')->default('demo@email.com'),
            )
            ->resolveFields($request)
            ->each(fn(AbstractField $field) => $field->resolveValueFromRequest($request));

        $this->assertEquals(
            [ 'first_name' => 'Hello', 'email' => 'demo@email.com' ],
            $response->pluck('value', 'attribute')->toArray()
        );

    }

    public function test_expect_fields_filter_works_correctly(): void
    {

        $resource = $this->makeResource();

        $request = $this->fieldsRequest($resource, [ 'except' => 'first_name , last_name' ]);

        $response = $resource
            ->addDefaultFields(
                EditableField::make('First Name')->default('Hello'),
                EditableField::make('Last Name')->default('World'),
                EditableField::make('Email')->default('demo@email.com'),
            )
            ->resolveFields($request)
            ->each(fn(AbstractField $field) => $field->resolveValueFromRequest($request));

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
