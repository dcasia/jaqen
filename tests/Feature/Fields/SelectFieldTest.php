<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\SelectField;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\User as UserResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

class SelectFieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;

    public function test_select_field_sends_the_options_correctly(): void
    {

        $response = [
            'label' => 'Gender',
            'attribute' => 'gender',
            'component' => 'select-field',
            'additionalInformation' => [
                'male' => 'Male',
                'female' => 'Female',
            ],
        ];

        $field = SelectField::make('Gender')->options([ 'male' => 'Male', 'female' => 'Female' ])->default('male');

        /**
         * On Create it should always use defaults if no date was sent
         */
        $field->resolveValueFromRequest($this->fieldsRequest(UserResource::uriKey()));
        $this->assertEquals($field->toArray(), array_merge($response, [ 'value' => 'male' ]));

        /**
         * On Update
         */
        $field->resolveValueFromRequest($this->updateRequest(UserResource::uriKey(), 1));
        $this->assertEquals($field->toArray(), array_merge($response, [ 'value' => null ]));

    }

    public function test_field_is_hydrated_correctly_from_model(): void
    {

        /**
         * @var UserModel $user
         */
        $user = factory(UserModel::class)->create();

        $field = SelectField::make('Gender')
                            ->options([ 'male' => 'Male', 'female' => 'Female' ])
                            ->resolveValueFromModel($user, $this->blankRequest());

        $this->assertSame($field->value, $user->gender);

    }

}
