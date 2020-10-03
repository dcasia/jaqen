<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\PasswordField;
use DigitalCreative\Dashboard\Http\Controllers\DetailController;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
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
         * @var UserModel $user
         */
        $user = factory(UserModel::class)->create();

        $resource = $this->makeResource()
                         ->addDefaultFields(PasswordField::make('Password'));

        $request = $this->detailRequest($resource::uriKey(), $user->id);

        $response = (new DetailController())->detail($request);

        $this->assertNull(data_get($response, 'fields.0.value'));

    }

}
