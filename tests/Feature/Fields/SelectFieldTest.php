<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields;

use DigitalCreative\Jaqen\Services\Fields\Fields\SelectField;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\UserResource;
use DigitalCreative\Jaqen\Tests\TestCase;

class SelectFieldTest extends TestCase
{
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

        $field = SelectField::make('Gender')
            ->options([ 'male' => 'Male', 'female' => 'Female' ])
            ->default('male');

        $resource = new UserResource();

        /**
         * On Create it should always use defaults if no date was sent
         */
        $field->resolveValueFromRequest($this->fieldsRequest($resource));
        $this->assertEquals($field->toArray(), array_merge($response, [ 'value' => 'male' ]));

        /**
         * On Update
         */
        $field->resolveValueFromRequest($this->updateRequest($resource, 1));
        $this->assertEquals($field->toArray(), array_merge($response, [ 'value' => null ]));
    }

    public function test_field_is_hydrated_correctly_from_model(): void
    {
        $user = UserFactory::new()->create();

        $field = SelectField::make('Gender')
            ->options([ 'male' => 'Male', 'female' => 'Female' ])
            ->resolveValueFromModel($user, $this->blankRequest());

        $this->assertSame($field->value, $user->gender);
    }
}
