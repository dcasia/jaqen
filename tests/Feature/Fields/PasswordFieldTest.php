<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields;

use DigitalCreative\Jaqen\Fields\PasswordField;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\TestCase;
use DigitalCreative\Jaqen\Tests\Traits\RequestTrait;
use DigitalCreative\Jaqen\Tests\Traits\ResourceTrait;

class PasswordFieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;

    public function test_password_field_does_not_send_value_through_the_response(): void
    {

        $user = UserFactory::new()->create();

        $resource = $this->makeResource()
                         ->addDefaultFields(PasswordField::make('Password'));

        $response = $this->updateResponse($resource, $user->id);

        $this->assertNull(data_get($response, 'fields.0.value'));

    }

}
