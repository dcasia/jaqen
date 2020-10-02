<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;

class ResourceFieldsTest extends TestCase
{

    public function test_fields_api_returns_correct_data(): void
    {

        factory(UserModel::class)->create();

        $this->get('/dashboard-api/users/fields?fieldsFor=index')
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
