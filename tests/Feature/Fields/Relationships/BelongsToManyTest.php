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
            [
                'label' => 'Hello World',
                'attribute' => 'hello_world',
                'value' => null,
                'component' => 'editable-field',
                'additionalInformation' => null,
            ],
        ], data_get($response, '0.relatedResource.pivotFields'));

    }

    public function test_storing_resource_works(): void
    {

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             BelongsToManyField::make('Roles')->setRelatedResource(RoleResource::class),
                         );

        $response = $this->storeResponse($resource, [
            'roles' => [
                [ 'fields' => [ 'name' => 'Admin' ] ],
                [ 'fields' => [ 'name' => 'Programmer' ] ],
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

        $this->assertEquals($expectedData, data_get($response, 'resources.0'));

        $this->assertEquals('user', data_get($response, 'resources.1.fields.0.relatedResource.resources.0.fields.0.value'));
        $this->assertEquals('test', data_get($response, 'resources.1.fields.0.relatedResource.resources.0.pivotFields.0.value'));

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
            [
                'label' => 'Extra',
                'attribute' => 'extra',
                'value' => 'sample-1',
                'component' => 'editable-field',
                'additionalInformation' => null,
            ],
        ];

        $this->assertEquals($expectedData, data_get($response, 'resources.0.fields.0.relatedResource.resources.0.pivotFields'));

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
