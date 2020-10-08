<?php

namespace DigitalCreative\Dashboard\Tests\Fixtures\Fields\Invokable;

use App\Dashboard\Resources\BlogResource;
use App\Dashboard\Resources\UserResource;
use App\Models\User;
use DigitalCreative\Dashboard\Fields\EditableField;

class SampleInvokable
{
    public function __invoke(): array
    {
        return [
            EditableField::make('Title')->rules('required'),
        ];
    }
}
