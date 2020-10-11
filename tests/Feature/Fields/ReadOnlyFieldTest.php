<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\EditableField;
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

    public function test_ready_only_field_stays_always_read_only(): void
    {
        $this->assertTrue(ReadOnlyField::make('id')->isReadOnly());
        $this->assertTrue(ReadOnlyField::make('id')->readOnly(false)->isReadOnly());
        $this->assertTrue(ReadOnlyField::make('id')->readOnly(fn() => false)->isReadOnly());
    }

    public function test_normal_fields_read_only_works(): void
    {
        $this->assertFalse(EditableField::make('id')->isReadOnly());

        $this->assertTrue(EditableField::make('id')->readOnly(true)->isReadOnly());
        $this->assertTrue(EditableField::make('id')->readOnly(fn() => true)->isReadOnly());

        $this->assertFalse(EditableField::make('id')->readOnly(fn() => false)->isReadOnly());
        $this->assertFalse(EditableField::make('id')->readOnly(null)->isReadOnly());

        $this->assertTrue(EditableField::make('id')->readOnly(1)->isReadOnly());
        $this->assertFalse(EditableField::make('id')->readOnly(0)->isReadOnly());
    }

}
