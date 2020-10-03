<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Fixtures\Resources;

use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\Resources\Resource;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Article as ArticleModel;
use Illuminate\Database\Eloquent\Model;

class Article extends Resource
{

    public function getModel(): Model
    {
        return new ArticleModel();
    }

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
