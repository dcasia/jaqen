<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers;

use DigitalCreative\Jaqen\Tests\TestCase;

class FiltersControllerTest extends TestCase
{

    public function test_resource_filters_works(): void
    {

        $this->getJson('/jaqen-api/resource/users/filters')
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
                                 'female',
                             ],
                         ],
                     ],
                 ],
             ]);

    }

}
