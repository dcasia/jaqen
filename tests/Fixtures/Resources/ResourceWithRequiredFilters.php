<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Fixtures\Resources;

use DigitalCreative\Dashboard\Fields\PasswordField;
use DigitalCreative\Dashboard\Resources\Resource;
use DigitalCreative\Dashboard\Tests\Fixtures\Filters\FilterWithRequiredFields;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use Illuminate\Database\Eloquent\Model;

class ResourceWithRequiredFilters extends Resource
{

    public function getModel(): Model
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
