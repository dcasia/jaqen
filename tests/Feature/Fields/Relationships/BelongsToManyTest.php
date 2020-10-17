<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields\Relationships;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\Relationships\BelongsToManyField;
use DigitalCreative\Dashboard\Http\Controllers\FieldsController;
use DigitalCreative\Dashboard\Tests\Factories\RoleFactory;
use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\RoleResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RelationshipRequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class BelongsToManyTest extends TestCase
{

    use RequestTrait;
    use RelationshipRequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

    public function test_it_returns_correct_data_on_fields_api(): void
    {

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class),
                         );

        $request = $this->fieldsRequest($resource);

        $response = (new FieldsController())->fields($request)->getData(true);

        $this->assertEquals([
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
                'relatedResourcePivot' => [
                    'attribute' => 'rolesPivot',
                    'fields' => [],
                ],
            ],
        ], $response);

    }

    public function test_pivot_data_returns_correctly_on_fields_api(): void
    {

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields(function() {
                                                   return [
                                                       new EditableField('Hello World'),
                                                   ];
                                               }),
                         );

        $request = $this->fieldsRequest($resource);

        $response = (new FieldsController())->fields($request)->getData(true);

        $this->assertEquals([
            'attribute' => 'rolesPivot',
            'fields' => [
                [
                    'label' => 'Hello World',
                    'attribute' => 'hello_world',
                    'value' => null,
                    'component' => 'editable-field',
                    'additionalInformation' => null,
                ],
            ],
        ], $response[0]['relatedResourcePivot']);

    }

    public function test_storing_resource_works(): void
    {

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')->setRelatedResource(RoleResource::class),
                         );

        $response = $this->storeResponse($resource, [
            'roles' => [
                [ 'name' => 'Admin' ],
                [ 'name' => 'Programmer' ],
            ],
        ]);

        $this->assertEquals([
            'id' => 1,
            'roles' => [
                [ 'id' => 1, 'name' => 'Admin' ],
                [ 'id' => 2, 'name' => 'Programmer' ],
            ],
        ], $response);

        $this->assertDatabaseHas('role_user', [ 'user_id' => 1, 'role_id' => 1 ]);
        $this->assertDatabaseHas('role_user', [ 'user_id' => 1, 'role_id' => 2 ]);

    }

    public function test_storing_pivot_fields_works(): void
    {

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([
                                                   new EditableField('Extra'),
                                               ]),
                         );

        $response = $this->storeResponse($resource, [
            'rolesPivot' => [
                [ 'extra' => 'sample' ],
                [],
            ],
            'roles' => [
                [ 'name' => 'Admin' ],
                [ 'name' => 'Programmer' ],
            ],
        ]);

        $this->assertEquals([
            'id' => 1,
            'roles' => [
                [ 'id' => 1, 'name' => 'Admin' ],
                [ 'id' => 2, 'name' => 'Programmer' ],
            ],
        ], $response);

        $this->assertDatabaseHas('role_user', [ 'user_id' => 1, 'role_id' => 1, 'extra' => 'sample' ]);
        $this->assertDatabaseHas('role_user', [ 'user_id' => 1, 'role_id' => 2, 'extra' => null ]);

    }

    public function test_an_error_is_thrown_when_pivot_attributes_and_data_length_differs(): void
    {

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([ new EditableField('Extra') ]),
                         );

        $this->expectExceptionMessage('Invalid attributes length.');

        $this->storeResponse($resource, [
            'rolesPivot' => [
                [ 'extra' => 'sample' ],
            ],
            'roles' => [
                [ 'name' => 'Admin' ],
                [ 'name' => 'Programmer' ],
            ],
        ]);

        $this->storeResponse($resource, [
            'rolesPivot' => [
                [ 'extra' => 'sample' ],
                [ 'extra' => 'sample' ],
            ],
            'roles' => [
                [ 'name' => 'Programmer' ],
            ],
        ]);

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

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([
                                                   new EditableField('Extra'),
                                               ]),
                         );
        $response = $this->indexResponse($resource);

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
                        'fields' => [
                            [
                                [
                                    'label' => 'Name',
                                    'attribute' => 'name',
                                    'value' => 'admin-1',
                                    'component' => 'editable-field',
                                    'additionalInformation' => null,
                                ],
                            ],
                            [
                                [
                                    'label' => 'Name',
                                    'attribute' => 'name',
                                    'value' => 'admin-2',
                                    'component' => 'editable-field',
                                    'additionalInformation' => null,
                                ],
                            ],
                        ],
                    ],
                    'relatedResourcePivot' => [
                        'attribute' => 'rolesPivot',
                        'fields' => [
                            [
                                [
                                    'label' => 'Extra',
                                    'attribute' => 'extra',
                                    'value' => 'sample-1',
                                    'component' => 'editable-field',
                                    'additionalInformation' => null,
                                ],
                            ],
                            [
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
        ];

        $this->assertEquals($expectedData, data_get($response, 'resources.0'));

        $this->assertEquals('user', data_get($response, 'resources.1.fields.0.relatedResource.fields.0.0.value'));
        $this->assertNull(data_get($response, 'resources.1.fields.0.relatedResource.fields.0.1.value'));

        $this->assertEquals('test', data_get($response, 'resources.1.fields.0.relatedResourcePivot.fields.0.0.value'));
        $this->assertNull(data_get($response, 'resources.1.fields.0.relatedResourcePivot.fields.0.1.value'));

    }

    public function test_it_works_with_custom_pivot_accessor(): void
    {

        UserFactory::new()->hasAttached(RoleFactory::new(), [ 'extra' => 'sample-1' ])->create();

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles', 'rolesWithCustomAccessor')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([
                                                   new EditableField('Extra'),
                                               ]),
                         );

        $response = $this->indexResponse($resource);

        $expectedData = [
            'attribute' => 'rolesWithCustomAccessorPivot',
            'fields' => [
                [
                    [
                        'label' => 'Extra',
                        'attribute' => 'extra',
                        'value' => 'sample-1',
                        'component' => 'editable-field',
                        'additionalInformation' => null,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedData, data_get($response, 'resources.0.fields.0.relatedResourcePivot'));

    }

    public function test_it_crashes_if_an_invalid_relationship_is_given(): void
    {

        UserFactory::new()->create();

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles', 'invalidRelation'),
                         );

        $this->expectException(RelationNotFoundException::class);

        $this->indexResponse($resource);

    }

    public function test_it_crashes_if_wrong_type_of_relationship_is_given(): void
    {

        UserFactory::new()->withPhone()->create();

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles', 'phone')
                                               ->setRelatedResource(RoleResource::class)
                                               ->setPivotFields([
                                                   new EditableField('Extra'),
                                               ]),
                         );

        $this->expectExceptionMessage('Invalid relationship type.');

        $this->indexResponse($resource);

    }

}
