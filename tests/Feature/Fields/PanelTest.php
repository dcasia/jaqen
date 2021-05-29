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
        $indexResponse = $this->indexResponse($resource);

        $this->assertEquals('panel', data_get($indexResponse, 'resources.0.fields.0.component'));
        $this->assertEquals('hello world', data_get($indexResponse, 'resources.0.fields.0.value.0.value'));

        /**
         * Detail Controller
         */
        $detailResponse = $this->detailResponse($resource, $user->getKey());

        $this->assertEquals('panel', data_get($detailResponse, 'fields.0.component'));
        $this->assertEquals('hello world', data_get($detailResponse, 'fields.0.value.0.value'));

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

        $this->storeResponse($resource, $data);

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

        $this->updateResponse($resource, $user->getKey(), $data);

        $this->assertDatabaseHas('users', $data);

    }

}
