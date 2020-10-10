<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\PasswordField;
use DigitalCreative\Dashboard\Http\Controllers\Resources\DetailController;
use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

class PasswordFieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;

    public function test_password_field_does_not_send_value_through_the_response(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->makeResource()
                         ->addDefaultFields(PasswordField::make('Password'));

        $request = $this->detailRequest($resource, $user->id);

        $response = (new DetailController())->handle($request);

        $this->assertNull(data_get($response, 'fields.0.value'));

    }

}
