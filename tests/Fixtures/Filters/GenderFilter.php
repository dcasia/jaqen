<?php

namespace DigitalCreative\Jaqen\Tests\Fixtures\Filters;

use DigitalCreative\Jaqen\Services\Fields\Fields\SelectField;
use DigitalCreative\Jaqen\Services\Fields\FieldsData;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class GenderFilter extends AbstractFilter
{
    public function apply(Builder $builder, FieldsData $value): Builder
    {
        return $builder->where('gender', $value->get('gender'));
    }

    public function fields(): array
    {
        return [
            SelectField::make('Gender')->options([
                'male' => 'Male',
                'female' => 'Female',
            ]),
        ];
    }
}
