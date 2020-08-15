<?php

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\TestCase;

class ResourceFiltersTest extends TestCase
{

    public function test_resource_filters_works(): void
    {

        $this->getJson('/dashboard-api/clients/filters')
             ->assertStatus(200)
             ->assertJsonStructure([
                 [
                     'uriKey',
                     'fields' => [
                         [
                             'label',
                             'attribute',
                             'value',
                             'component',
                             'additionalInformation' => [
                                 'male',
                                 'female'
                             ],
                         ]
                     ]
                 ]
             ]);

    }

}
