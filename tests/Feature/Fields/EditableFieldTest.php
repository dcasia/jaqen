<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\User;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\User as UserResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

class EditableFieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;

    public function test_editable_field_works(): void
    {

        $data = [
            'name' => 'test',
            'email' => 'email@email.com',
            'gender' => 'male',
            'password' => 123456
        ];

        $request = $this->createRequest(UserResource::uriKey(), $data);

        $this->makeResource($request)
             ->addFields(
                 (new EditableField('Name'))->rulesForCreate('required'),
                 (new EditableField('Email'))->rulesForCreate('required'),
                 (new EditableField('Gender'))->rulesForCreate('required'),
                 (new EditableField('Password'))->rulesForCreate('required'),
             )
             ->create();

        $this->assertDatabaseHas('users', $data);

    }

    public function test_editable_field_on_update_works(): void
    {

        /**
         * @var UserModel $user
         */
        $user = factory(UserModel::class)->create();

        $request = $this->updateRequest(UserResource::uriKey(), $user->id, [ 'name' => 'updated' ]);

        $this->makeResource($request, UserModel::class)
             ->addFields(
                 new EditableField('Name'),
                 new EditableField('Email'),
                 new EditableField('Gender'),
             )
             ->update();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
            'name' => 'updated'
        ]);

    }

}
