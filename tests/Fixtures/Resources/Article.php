<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Fixtures\Resources;

use DigitalCreative\Dashboard\AbstractResource;
use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Article as ArticleModel;

class Article extends AbstractResource
{

    public static $model = ArticleModel::class;

    public function fields(): array
    {
        return [
            ReadOnlyField::make('id'),
            EditableField::make('Title'),
            EditableField::make('Content'),
            BelongsToField::make('User', 'user', User::class)
        ];
    }

}
