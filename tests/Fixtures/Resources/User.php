<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Fixtures\Resources;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\PasswordField;
use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\Fields\SelectField;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Tests\Fixtures\Filters\GenderFilter;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use Illuminate\Database\Eloquent\Model;

class User extends AbstractResource
{

    public function model(): Model
    {
        return new UserModel();
    }

    public function fields(): array
    {
        return [
            ReadOnlyField::make('id'),
            EditableField::make('name'),
            EditableField::make('email')->rules('email'),
            SelectField::make('gender')->options([ 'male' => 'Male', 'female' => 'Female' ]),
            PasswordField::make('password'),
        ];
    }

    public function fieldsForIndex(): array
    {
        return [
            ReadOnlyField::make('id'),
        ];
    }

    public function filters(): array
    {
        return [
            new GenderFilter(),
        ];
    }

}
