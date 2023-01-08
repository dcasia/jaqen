<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields;

use DigitalCreative\Jaqen\Services\Fields\Fields\PasswordField;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\TestCase;

class PasswordFieldTest extends TestCase
{
    public function test_password_field_does_not_send_value_through_the_response(): void
    {
        $user = UserFactory::new()->create();

        $resource = $this->makeResource()
            ->addDefaultFields(PasswordField::make('Password'));

        $this->resourceUpdateApi($resource, $user->id)
            ->assertOk()
            ->assertJsonPath('fields.0.value', null);
    }
}
