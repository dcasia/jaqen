<?php

namespace DigitalCreative\Dashboard\Tests\Fixtures\Resources;

use DigitalCreative\Dashboard\AbstractResource;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Client as ClientModel;

class ClientWithFields extends AbstractResource
{

    public static $model = ClientModel::class;

    public function fields(): array
    {
        return [
            new ReadOnlyField('id'),
            new EditableField('name'),
            new EditableField('email'),
            new EditableField('gender'),
        ];
    }

}
