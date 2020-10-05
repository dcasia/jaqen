<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Http\Controllers\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\UpdateController;
use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
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
            'password' => 123456,
        ];

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             EditableField::make('Name')->rulesForCreate('required'),
                             EditableField::make('Email')->rulesForCreate('required'),
                             EditableField::make('Gender')->rulesForCreate('required'),
                             EditableField::make('Password')->rulesForCreate('required'),
                         );

        $request = $this->storeRequest($resource::uriKey(), $data);

        (new StoreController())->store($request);

        $this->assertDatabaseHas('users', $data);

    }

    public function test_editable_field_on_update_works(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             new EditableField('Name'),
                             new EditableField('Email'),
                             new EditableField('Gender'),
                         );

        $request = $this->updateRequest($resource::uriKey(), $user->id, [ 'name' => 'updated' ]);

        (new UpdateController())->update($request);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
            'name' => 'updated',
        ]);

    }

}
