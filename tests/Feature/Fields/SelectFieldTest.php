<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\SelectField;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;

class SelectFieldTest extends TestCase
{

    use RequestTrait;

    public function test_select_field_sends_the_options_correctly(): void
    {

        $field = SelectField::make('Gender')
                            ->options([ 'male' => 'Male', 'female' => 'Female' ]);

        $this->assertSame($field->jsonSerialize(), [
            'label' => 'Gender',
            'attribute' => 'gender',
            'value' => null,
            'component' => 'select-field',
            'additionalInformation' => [
                'male' => 'Male',
                'female' => 'Female'
            ]
        ]);

    }

    public function test_field_is_hydrated_correctly_from_model(): void
    {

        /**
         * @var UserModel $user
         */
        $user = factory(UserModel::class)->create();

        $field = SelectField::make('Gender')
                            ->options([ 'male' => 'Male', 'female' => 'Female' ])
                            ->resolveUsingModel($this->blankRequest(),$user);

        $this->assertSame($field->value, $user->gender);

    }

}
