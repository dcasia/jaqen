<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Fixtures\Resources;

use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Services\Fields\Fields\PasswordField;
use DigitalCreative\Jaqen\Services\Fields\Fields\ReadOnlyField;
use DigitalCreative\Jaqen\Services\Fields\Fields\SelectField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Filters\GenderFilter;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
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
            GenderFilter::make(),
        ];
    }

}
