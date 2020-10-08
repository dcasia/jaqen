<?php

namespace DigitalCreative\Dashboard\Tests\Fixtures\Filters;

use DigitalCreative\Dashboard\Fields\EditableField;

class FilterWithRequiredFields extends SampleFilter
{

    public function fields(): array
    {
        return [
            EditableField::make('Name')->rules('required'),
        ];
    }

}
