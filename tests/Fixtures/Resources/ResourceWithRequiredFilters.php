<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Resources;

use DigitalCreative\Jaqen\Fields\PasswordField;
use DigitalCreative\Jaqen\Resources\AbstractResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Filters\FilterWithRequiredFields;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use Illuminate\Database\Eloquent\Model;

class ResourceWithRequiredFilters extends AbstractResource
{

    public function model(): Model
    {
        return new UserModel();
    }

    public function fields(): array
    {
        return [
            PasswordField::make('password'),
        ];
    }

    public function filters(): array
    {
        return [
            new FilterWithRequiredFields(),
        ];
    }

}
