<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\Http\Controllers\Resources\UpdateController;
use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

class ReadOnlyFieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;

    public function test_read_only_field_works(): void
    {

        $user = UserFactory::new()->create([ 'name' => 'original' ]);

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             ReadOnlyField::make('Name')->rulesForUpdate('required'),
                             ReadOnlyField::make('Email')->rules('required'),
                             ReadOnlyField::make('Gender'),
                         );

        $request = $this->updateRequest($resource, $user->id, [ 'name' => 'updated' ]);

        (new UpdateController())->handle($request);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'original',
        ]);

    }

}
