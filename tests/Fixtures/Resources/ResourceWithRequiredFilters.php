<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Fixtures\Resources;

use DigitalCreative\Dashboard\AbstractResource;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\PasswordField;
use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\Fields\SelectField;
use DigitalCreative\Dashboard\Tests\Fixtures\Filters\FilterWithRequiredFields;
use DigitalCreative\Dashboard\Tests\Fixtures\Filters\GenderFilter;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;

class ResourceWithRequiredFilters extends AbstractResource
{

    public static $model = UserModel::class;

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
