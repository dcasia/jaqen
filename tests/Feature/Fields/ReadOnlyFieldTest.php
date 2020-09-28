<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\User as UserResource;
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
         * @var User $user
         */
        $user = factory(User::class)->create([ 'name' => 'original' ]);

        $request = $this->updateRequest(UserResource::uriKey(), $user->id, [ 'name' => 'updated' ]);

        $this->makeResource($request)
             ->addDefaultFields(
                 ReadOnlyField::make('Name')->rulesForUpdate('required'),
                 ReadOnlyField::make('Email')->rules('required'),
                 ReadOnlyField::make('Gender'),
             )
             ->update();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'original'
        ]);

    }

}
