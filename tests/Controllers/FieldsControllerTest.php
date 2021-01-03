<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers;

use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\TestCase;

class FieldsControllerTest extends TestCase
{

    public function test_fields_api_returns_correct_data(): void
    {

        UserFactory::new()->create();

        $this->get('/jaqen-api/resource/users/fields?fieldsFor=index')
             ->assertStatus(200)
             ->assertJsonFragment([
                 [
                     'label' => 'id',
                     'attribute' => 'id',
                     'value' => null,
                     'component' => 'read-only-field',
                     'additionalInformation' => null,
                 ],
             ]);

    }

}
