<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Resources;

use DigitalCreative\Jaqen\Fields\EditableField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Role as RoleModel;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends AbstractResource
{

    public function model(): Model
    {
        return new RoleModel();
    }

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
