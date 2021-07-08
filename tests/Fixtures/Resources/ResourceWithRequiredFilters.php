<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Resources;

use DigitalCreative\Jaqen\Services\Fields\Fields\PasswordField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Filters\FilterWithRequiredFields;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;

class ResourceWithRequiredFilters extends AbstractResource
{

    public static string $model = UserModel::class;

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
