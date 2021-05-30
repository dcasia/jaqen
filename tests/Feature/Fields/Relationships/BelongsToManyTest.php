<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields\Relationships;

use DigitalCreative\Jaqen\Fields\Relationships\BelongsToManyField;
use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Tests\Factories\RoleFactory;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\RoleResource;
use DigitalCreative\Jaqen\Tests\TestCase;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Validation\ValidationException;

class BelongsToManyTest extends TestCase
{

    public function test_it_returns_correct_data_on_fields_api(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class),
                         );

        $this->resourceFieldsApi($resource)
             ->assertJson([
                 [
                     'label' => 'Roles',
                     'attribute' => 'roles',
                     'value' => null,
                     'component' => 'belongs-to-many-field',
                     'additionalInformation' => null,
                     'searchable' => false,
                     'relatedResource' => [
                         'name' => 'Role Resource',
                         'label' => 'Role Resources',
                         'uriKey' => 'role-resources',
                         'pivotFields' => [],
                         'fields' => [
                             [
                                 'label' => 'Name',
                                 'attribute' => 'name',
                                 'value' => null,
                                 'component' => 'editable-field',
                                 'additionalInformation' => null,
                             ],
                         ],
                     ],
                 ],
             ]);

    }

    public function test_pivot_data_returns_correctly_on_fields_api(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields(function () {
                                                   return [
                                                       new EditableField('Hello World'),
                                                   ];
                                               }),
                         );

        $this->resourceFieldsApi($resource)
             ->assertJsonPath('0.relatedResource.pivotFields', [
                 [
                     'label' => 'Hello World',
                     'attribute' => 'hello_world',
                     'value' => null,
                     'component' => 'editable-field',
                     'additionalInformation' => null,
                 ],
             ]);

    }

    public function test_storing_resource_works(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')->setRelatedResource(RoleResource::class),
                         );

        $response = $this->resourceCreateApi($resource, [
            'roles' => [
                [ 'fields' => [ 'name' => 'Admin' ] ],
                [ 'fields' => [ 'name' => 'Programmer' ] ],
            ],
        ]);

        $response->assertJson([
            'id' => 1,
            'roles' => [
                [ 'id' => 1, 'name' => 'Admin' ],
                [ 'id' => 2, 'name' => 'Programmer' ],
            ],
        ]);

        $this->assertDatabaseHas('role_user', [ 'user_id' => 1, 'role_id' => 1 ]);
        $this->assertDatabaseHas('role_user', [ 'user_id' => 1, 'role_id' => 2 ]);

    }

    public function test_validation_works(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->rules('required'),
                         );

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $this->resourceCreateApi($resource, [ 'roles' => null ])
             ->assertStatus(422)
             ->assertJsonFragment([
                 'roles' => [ 'The roles field is required.' ],
             ]);

    }

    public function test_resource_count_validation_works(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->rules([ 'min:3' ]),
                         );

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $response = $this->resourceCreateApi($resource, [
            'roles' => [
                [ 'fields' => [ 'name' => 1 ] ],
                [ 'fields' => [ 'name' => 2 ] ],
            ],
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'message' => 'The given data was invalid.',
                 ]);

    }

    public function test_related_resource_validation_works(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setRelatedResourceFieldsFor('fieldsWithValidation'),
                         );

        $response = $this->resourceCreateApi($resource, [
            'roles' => [
                [ 'fields' => [ 'name' => null ] ],
                [ 'fields' => [ 'name' => 'Programmer' ] ],
            ],
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'message' => 'The given data was invalid.',
                     'errors' => [
                         'roles' => [
                             'fields' => [
                                 'name' => [ 'The name field is required.' ],
                             ],
                         ],
                     ],
                 ]);

    }

    public function test_validation_works_on_pivot_fields(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([
                                                   EditableField::make('Extra')->rules('required'),
                                               ]),
                         );

        $response = $this->resourceCreateApi($resource, [
            'roles' => [
                [ 'fields' => [ 'name' => 'Admin' ] ],
                [ 'fields' => [ 'name' => 'Programmer' ] ],
            ],
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'pivotFields' => [
                         'extra' => [ 'The extra field is required.' ],
                     ],
                 ]);

    }

    public function test_validation_response_is_correctly_prefixed_with_field_name(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class, 'fieldsWithValidation')
                                               ->setPivotFields([
                                                   EditableField::make('Extra')->rules('required'),
                                               ]),
                         );

        $response = $this->resourceCreateApi($resource, [
            'roles' => [
                [ 'fields' => [ 'name' => null ] ],
            ],
        ]);

        $response->assertJsonFragment([
            'message' => 'The given data was invalid.',
            'errors' => [
                'roles' => [
                    'fields' => [
                        'name' => [ 'The name field is required.' ],
                    ],
                    'pivotFields' => [
                        'extra' => [ 'The extra field is required.' ],
                    ],
                ],
            ],
        ]);

    }

    public function test_it_works_correctly_simulating_a_real_call(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class, 'fieldsWithValidation')
                                               ->setPivotFields([
                                                   EditableField::make('Extra')->rules('required'),
                                               ]),
                         );

        $response = $this->resourceCreateApi($resource, [
            'roles' => [
                [
                    'fields' => [ 'name' => 'admin' ],
                    'pivotFields' => [ 'extra' => 'test' ],
                ],
            ],
        ]);

        $response->assertJsonFragment([
            'roles' => [
                [ 'id' => 1, 'name' => 'admin' ],
            ],
        ]);

    }

    public function test_file_upload_works_on_related_resource_and_on_pivot_fields(): void
    {
        $this->markTestIncomplete('Test if files uploads works when creating related resource, as the request is duplicated a few times maybe files attachments could get lost.');
    }

    public function test_validation_works_on_update(): void
    {
        $this->markTestIncomplete('Test if validation is working when updating an existing resource.');
    }

    public function test_storing_pivot_fields_works(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([
                                                   new EditableField('Extra'),
                                               ]),
                         );

        $response = $this->resourceCreateApi($resource, [
            'roles' => [
                [
                    'fields' => [ 'name' => 'Admin' ],
                    'pivotFields' => [ 'extra' => 'sample' ],
                ],
                [
                    'fields' => [ 'name' => 'Programmer' ],
                    'pivotFields' => [],
                ],
            ],
        ]);

        $response->assertJson([
            'id' => 1,
            'roles' => [
                [ 'id' => 1, 'name' => 'Admin' ],
                [ 'id' => 2, 'name' => 'Programmer' ],
            ],
        ]);

        $this->assertDatabaseHas('role_user', [ 'user_id' => 1, 'role_id' => 1, 'extra' => 'sample' ]);
        $this->assertDatabaseHas('role_user', [ 'user_id' => 1, 'role_id' => 2, 'extra' => null ]);

    }

    public function test_pivot_fields_are_hydrated_on_index_listing(): void
    {

        UserFactory::new()
                   ->hasAttached(RoleFactory::new()->state([ 'name' => 'admin-1' ]), [ 'extra' => 'sample-1' ])
                   ->hasAttached(RoleFactory::new()->state([ 'name' => 'admin-2' ]), [ 'extra' => 'sample-2' ])
                   ->create();

        UserFactory::new()
                   ->hasAttached(RoleFactory::new()->state([ 'name' => 'user' ]), [ 'extra' => 'test' ])
                   ->create();

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([
                                                   new EditableField('Extra'),
                                               ]),
                         );

        $response = $this->resourceIndexApi($resource);

        $expectedData = [
            'key' => 1,
            'fields' => [
                [
                    'label' => 'Roles',
                    'attribute' => 'roles',
                    'value' => null,
                    'component' => 'belongs-to-many-field',
                    'additionalInformation' => null,
                    'searchable' => false,
                    'relatedResource' => [
                        'name' => 'Role Resource',
                        'label' => 'Role Resources',
                        'uriKey' => 'role-resources',
                        'resources' => [
                            [
                                'key' => 1,
                                'fields' => [
                                    [
                                        'label' => 'Name',
                                        'attribute' => 'name',
                                        'value' => 'admin-1',
                                        'component' => 'editable-field',
                                        'additionalInformation' => null,
                                    ],
                                ],
                                'pivotFields' => [
                                    [
                                        'label' => 'Extra',
                                        'attribute' => 'extra',
                                        'value' => 'sample-1',
                                        'component' => 'editable-field',
                                        'additionalInformation' => null,
                                    ],
                                ],
                            ],
                            [
                                'key' => 2,
                                'fields' => [
                                    [
                                        'label' => 'Name',
                                        'attribute' => 'name',
                                        'value' => 'admin-2',
                                        'component' => 'editable-field',
                                        'additionalInformation' => null,
                                    ],
                                ],
                                'pivotFields' => [
                                    [
                                        'label' => 'Extra',
                                        'attribute' => 'extra',
                                        'value' => 'sample-2',
                                        'component' => 'editable-field',
                                        'additionalInformation' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response->assertJsonPath('resources.0', $expectedData)
                 ->assertJsonPath('resources.1.fields.0.relatedResource.resources.0.fields.0.value', 'user')
                 ->assertJsonPath('resources.1.fields.0.relatedResource.resources.0.pivotFields.0.value', 'test');

    }

    public function test_it_works_with_custom_pivot_accessor(): void
    {

        UserFactory::new()->hasAttached(RoleFactory::new(), [ 'extra' => 'sample-1' ])->create();

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles', 'rolesWithCustomAccessor')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([
                                                   new EditableField('Extra'),
                                               ]),
                         );

        $response = $this->resourceIndexApi($resource);

        $expectedData = [
            [
                'label' => 'Extra',
                'attribute' => 'extra',
                'value' => 'sample-1',
                'component' => 'editable-field',
                'additionalInformation' => null,
            ],
        ];

        $response->assertJsonPath('resources.0.fields.0.relatedResource.resources.0.pivotFields', $expectedData);

    }

    public function test_it_crashes_if_an_invalid_relationship_is_given(): void
    {

        UserFactory::new()->create();

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles', 'invalidRelation'),
                         );

        $this->withoutExceptionHandling();
        $this->expectException(RelationNotFoundException::class);

        $this->resourceIndexApi($resource)->assertStatus(500);

    }

    public function test_it_crashes_if_wrong_type_of_relationship_is_given(): void
    {

        UserFactory::new()->withPhone()->create();

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles', 'phone')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([
                                                   new EditableField('Extra'),
                                               ]),
                         );

        $this->withoutExceptionHandling();
        $this->expectExceptionMessage('Invalid relationship type.');

        $this->resourceIndexApi($resource)->assertStatus(500);

    }

    public function test_it_can_update_related_resources(): void
    {

        $user = UserFactory::new()
                           ->hasAttached(RoleFactory::new()->state([ 'name' => 'admin-1' ]), [ 'extra' => 'sample-1' ])
                           ->hasAttached(RoleFactory::new()->state([ 'name' => 'admin-2' ]), [ 'extra' => 'sample-2' ])
                           ->create();

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([
                                                   new EditableField('Extra'),
                                               ]),
                         );

        $updatedData = [
            'roles' => [
                [
                    'key' => 1,
                    'fields' => [
                        'name' => 'admin-a',
                    ],
                    'pivotFields' => [
                        'extra' => 'sample-a',
                    ],
                ],
                [
                    'key' => 2,
                    'fields' => [
                        'name' => 'admin-b',
                    ],
                    'pivotFields' => [
                        'extra' => 'sample-b',
                    ],
                ],
            ],
        ];

        $this->resourceUpdateApi($resource, $user->id, $updatedData)->assertOk();

        $this->assertDatabaseHas('roles', [ 'id' => 1, 'name' => 'admin-a' ]);
        $this->assertDatabaseHas('roles', [ 'id' => 2, 'name' => 'admin-b' ]);

        $this->assertDatabaseHas('role_user', [ 'user_id' => 1, 'role_id' => 1, 'extra' => 'sample-a' ]);
        $this->assertDatabaseHas('role_user', [ 'user_id' => 1, 'role_id' => 2, 'extra' => 'sample-b' ]);

    }

}
