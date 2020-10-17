<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Fixtures\Resources;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Phone as PhoneModel;
use Illuminate\Database\Eloquent\Model;

class PhoneResource extends AbstractResource
{

    public function model(): Model
    {
        return new PhoneModel();
    }

    public function fields(): array
    {
        return [
            EditableField::make('Number'),
        ];
    }

    public function fieldsForFieldsWithValidation(): array
    {
        return [
            EditableField::make('Number')->rules([ 'numeric' ]),
        ];
    }

}
