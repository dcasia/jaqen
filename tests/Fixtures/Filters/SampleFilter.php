<?php

namespace DigitalCreative\Jaqen\Tests\Fixtures\Filters;

use DigitalCreative\Jaqen\AbstractFilter;
use DigitalCreative\Jaqen\FieldsData;
use Illuminate\Database\Eloquent\Builder;

class SampleFilter extends AbstractFilter
{

    public function apply(Builder $builder, FieldsData $fieldsData): Builder
    {
        return $builder;
    }

}
