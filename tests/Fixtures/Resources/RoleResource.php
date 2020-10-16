<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Fixtures\Resources;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Role as RoleModel;
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

}
