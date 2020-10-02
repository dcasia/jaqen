<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\Tests\TestCase;

class ReadOnlyFieldTest extends TestCase
{

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
