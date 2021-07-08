<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Resources;

use DigitalCreative\Jaqen\Fields\Relationships\BelongsToField;
use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Services\Fields\Fields\ReadOnlyField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article as ArticleModel;

class Article extends AbstractResource
{

    public static string $model = ArticleModel::class;

    public function fields(): array
    {
        return [
            ReadOnlyField::make('id'),
            EditableField::make('Title'),
            EditableField::make('Content'),
            BelongsToField::make('User', 'user', User::class),
        ];
    }

}
