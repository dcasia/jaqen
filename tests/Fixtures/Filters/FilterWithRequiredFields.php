<?php

namespace DigitalCreative\Dashboard\Tests\Fixtures\Filters;

use DigitalCreative\Dashboard\AbstractFilter;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\SelectField;
use DigitalCreative\Dashboard\FieldsData;
use Illuminate\Database\Eloquent\Builder;

class FilterWithRequiredFields extends SampleFilter
{

    public function fields(): array
    {
        return [
            EditableField::make('Name')->rules('required'),
        ];
    }

}
