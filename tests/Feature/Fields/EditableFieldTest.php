<?php

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\PasswordField;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Client as ClientModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\Client as ClientResource;
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

        $request = $this->createRequest(ClientResource::uriKey(), $data);

        $this->getResource($request)
             ->addFields(
                 (new EditableField('Name'))->rulesForCreate('required'),
                 (new EditableField('Email'))->rulesForCreate('required'),
                 (new EditableField('Gender'))->rulesForCreate('required'),
                 (new EditableField('Password'))->rulesForCreate('required'),
             )
             ->create();

        $this->assertDatabaseHas('clients', $data);

    }

    public function test_editable_field_on_update_works(): void
    {

        /**
         * @var ClientModel $client
         */
        $client = factory(ClientModel::class)->create();

        $request = $this->makeRequest([
            '/{resource}/{key}' => "/clients/{$client->id}" ], 'POST', [ 'name' => 'updated' ], UpdateResourceRequest::class
        );

        $this->getResource($request, ClientModel::class)
             ->addFields(
                 new EditableField('Name'),
                 new EditableField('Email'),
                 new EditableField('Gender'),
             )
             ->update();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'email' => $client->email,
            'name' => 'updated'
        ]);

    }

}
