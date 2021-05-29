<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers;

use DigitalCreative\Jaqen\Tests\Fixtures\Resources\User;
use DigitalCreative\Jaqen\Tests\TestCase;

class FieldsControllerTest extends TestCase
{

    public function test_fields_api_returns_correct_data(): void
    {

        $this->registerResource(User::class);

        $this->resourceFieldsApi(User::class, fieldsFor: 'index')
             ->assertStatus(200)
             ->assertJsonCount(1)
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
