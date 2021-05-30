<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Resources;

use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Role as RoleModel;

class RoleResource extends AbstractResource
{

    public static string $model = RoleModel::class;

    public function fields(): array
    {
        return [
            EditableField::make('Name'),
        ];
    }

    public function fieldsForFieldsWithValidation(): array
    {
        return [
            EditableField::make('Name')->rules('required'),
        ];
    }

}
