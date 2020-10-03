<?php

namespace DigitalCreative\Dashboard\Tests\Fixtures\Filters;

use DigitalCreative\Dashboard\AbstractFilter;
use DigitalCreative\Dashboard\FieldsData;
use Illuminate\Database\Eloquent\Builder;

class SampleFilter extends AbstractFilter
{

    public function apply(Builder $builder, FieldsData $fieldsData): Builder
    {
        return $builder;
    }

}
