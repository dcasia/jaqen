<?php

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Client;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

class ReadOnlyFieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;

    public function test_read_only_field_works(): void
    {

        /**
         * @var Client $client
         */
        $client = factory(Client::class)->create([ 'name' => 'original' ]);

        $request = $this->updateRequest("/clients/$client->id", [ 'name' => 'updated' ]);

        $this->getResource($request)
             ->addFields(
                 ReadOnlyField::make('Name')->rulesForUpdate('required'),
                 ReadOnlyField::make('Email')->rules('required'),
                 ReadOnlyField::make('Gender'),
             )
             ->update();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'original'
        ]);

    }

}
