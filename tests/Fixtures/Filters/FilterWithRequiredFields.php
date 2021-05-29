<?php

namespace DigitalCreative\Jaqen\Tests\Fixtures\Filters;

use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;

class FilterWithRequiredFields extends SampleFilter
{

    public function fields(): array
    {
        return [
            EditableField::make('Name')->rules('required'),
        ];
    }

}
