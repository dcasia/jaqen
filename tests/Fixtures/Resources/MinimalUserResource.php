<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Fixtures\Resources;

use DigitalCreative\Dashboard\AbstractResource;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;

class MinimalUserResource extends AbstractResource
{

    public static $model = UserModel::class;

    public function fields(): array
    {
        return [
            EditableField::make('Name'),
        ];
    }

}
