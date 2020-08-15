<?php

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\SelectField;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Client as ClientModel;
use DigitalCreative\Dashboard\Tests\TestCase;

class SelectFieldTest extends TestCase
{

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
         * @var ClientModel $client
         */
        $client = factory(ClientModel::class)->create();

        $field = SelectField::make('Gender')
                            ->options([ 'male' => 'Male', 'female' => 'Female' ])
                            ->resolveUsingModel($client);

        $this->assertSame($field->value, $client->gender);

    }

}
