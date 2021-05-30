<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields;

use DigitalCreative\Jaqen\Fields\Panel;
use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Jaqen\Tests\TestCase;

class PanelTest extends TestCase
{

    public function test_panel_field_works_correctly(): void
    {

        $panel = Panel::make('Personal Information', [ EditableField::make('Name') ]);

        $response = $panel->resolveValueFromRequest($this->blankRequest())
                          ->toArray();

        $this->assertInstanceOf(EditableField::class, $response['value'][0]);

    }

    public function test_on_listing_or_details_panel_returns_data_correctly(): void
    {

        /**
         * @var UserModel $user
         */
        $user = UserFactory::new()->create([ 'name' => 'hello world' ]);

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             Panel::make('Personal Information', [
                                 EditableField::make('Name'),
                             ])
                         );

        /**
         * Index Controller
         */
        $indexResponse = $this->resourceIndexApi($resource);

        $indexResponse->assertJsonPath('resources.0.fields.0.component', 'panel');
        $indexResponse->assertJsonPath('resources.0.fields.0.value.0.value', 'hello world');

        /**
         * Detail Controller
         */
        $detailResponse = $this->resourceDetailApi($resource, $user->getKey());

        $detailResponse->assertJsonPath('fields.0.component', 'panel');
        $detailResponse->assertJsonPath('fields.0.value.0.value', 'hello world');

    }

    public function test_on_store_the_value_if_inner_fields_are_proxied_correctly(): void
    {

        $data = [
            'name' => 'Hello World',
            'email' => 'test@email.com',
            'gender' => 'male',
            'password' => 123,
        ];

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             Panel::make('Personal Information', [
                                 EditableField::make('Name')->rules('required'),
                                 EditableField::make('Email')->rules('required'),
                                 EditableField::make('Gender')->rules('required'),
                                 EditableField::make('Password')->rules('required'),
                             ])
                         );

        $this->resourceCreateApi($resource, $data)->assertCreated();

        $this->assertDatabaseHas('users', $data);

    }

    public function test_on_store_the_value_if_inner_fields_are_proxied_correctly_on_update(): void
    {

        $user = UserFactory::new()->create();

        $data = [
            'name' => 'Hello World',
        ];

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             Panel::make('Personal Information', [
                                 EditableField::make('Name'),
                             ])
                         );

        $this->resourceUpdateApi($resource, $user->getKey(), $data)->assertOk();

        $this->assertDatabaseHas('users', $data);

    }

}
