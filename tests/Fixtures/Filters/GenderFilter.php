<?php

namespace DigitalCreative\Dashboard\Tests\Fixtures\Filters;

use DigitalCreative\Dashboard\AbstractFilter;
use DigitalCreative\Dashboard\Fields\SelectField;
use DigitalCreative\Dashboard\FieldsData;
use Illuminate\Database\Eloquent\Builder;

class GenderFilter extends AbstractFilter
{

    public function apply(Builder $builder, FieldsData $fieldsData): Builder
    {
        return $builder->where('gender', $fieldsData->get('gender'));
    }

    public function fields(): array
    {
        return [
            (new SelectField('Gender'))->options([ 'male' => 'Male', 'female' => 'Female' ])
        ];
    }

}
