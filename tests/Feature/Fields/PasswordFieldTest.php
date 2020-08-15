<?php

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\PasswordField;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Client as ClientModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\Client as ClientResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

class PasswordFieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;

    public function test_password_field_does_not_send_value_through_the_response(): void
    {

        /**
         * @var ClientModel $client
         */
        $client = factory(ClientModel::class)->create();

        $request = $this->detailRequest(ClientResource::uriKey(), $client->id);

        $response = $this->getResource($request)
                         ->addFields(PasswordField::make('Password'))
                         ->detail();

        $this->assertNull(data_get($response, 'fields.0.value'));

    }

}
