<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields;

use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Services\Fields\Fields\ReadOnlyField;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User;
use DigitalCreative\Jaqen\Tests\TestCase;

class ReadOnlyFieldTest extends TestCase
{
    public function test_read_only_field_works(): void
    {
        $user = UserFactory::new()->create([ 'name' => 'original' ]);

        $resource = $this->makeResource()
            ->addDefaultFields(
                ReadOnlyField::make('Name')->rulesForUpdate('required'),
                ReadOnlyField::make('Email')->rules('required'),
                ReadOnlyField::make('Gender'),
            );

        $this->resourceUpdateApi($resource, $user->id, [ 'name' => 'updated' ])
            ->assertOk();

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'name' => 'original',
        ]);
    }

    public function test_ready_only_field_stays_always_read_only(): void
    {
        $this->assertTrue(ReadOnlyField::make('id')->isReadOnly());
        $this->assertTrue(ReadOnlyField::make('id')->readOnly(false)->isReadOnly());
        $this->assertTrue(ReadOnlyField::make('id')->readOnly(fn () => false)->isReadOnly());
    }

    public function test_normal_fields_read_only_works(): void
    {
        $this->assertFalse(EditableField::make('id')->isReadOnly());

        $this->assertTrue(EditableField::make('id')->readOnly(true)->isReadOnly());
        $this->assertTrue(EditableField::make('id')->readOnly(fn () => true)->isReadOnly());

        $this->assertFalse(EditableField::make('id')->readOnly(fn () => false)->isReadOnly());
        $this->assertFalse(EditableField::make('id')->readOnly(false)->isReadOnly());

        $this->assertTrue(EditableField::make('id')->readOnly(true)->isReadOnly());
        $this->assertFalse(EditableField::make('id')->readOnly(false)->isReadOnly());
    }
}
