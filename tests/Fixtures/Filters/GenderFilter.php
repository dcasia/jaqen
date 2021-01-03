<?php

namespace DigitalCreative\Jaqen\Tests\Fixtures\Filters;

use DigitalCreative\Jaqen\Services\ResourceManager\AbstractFilter;
use DigitalCreative\Jaqen\Services\Fields\SelectField;
use DigitalCreative\Jaqen\Services\Fields\FieldsData;
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
