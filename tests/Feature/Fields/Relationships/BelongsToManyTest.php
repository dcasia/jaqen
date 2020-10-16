<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields\Relationships;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\Relationships\BelongsToManyField;
use DigitalCreative\Dashboard\Http\Controllers\FieldsController;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\RoleResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RelationshipRequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

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

    public function test_a_error_is_thrown_when_pivot_attributes_and_data_length_differs(): void
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

}
