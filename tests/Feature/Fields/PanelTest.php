<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields;

use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Services\Fields\Fields\Panel;
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

    public function test_panel_returns_sub_fields_on_fields_api(): void
    {
        $resource = $this->makeResource()
                         ->addDefaultFields(
                             Panel::make('Panel 1', fn() => [ EditableField::make('Field 1') ]),
                             Panel::make('Panel 2', [ EditableField::make('Field 2') ])
                         );

        $this->resourceFieldsApi($resource)
             ->assertJsonPath('0.value.0.attribute', 'field_1')
             ->assertJsonPath('1.value.0.attribute', 'field_2');
    }

    public function test_if_field_is_unauthorized_its_fields_are_not_returned(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             Panel::make('Panel', [ EditableField::make('field-1') ])->canSee(fn() => false)
                         );

        $this->resourceFieldsApi($resource)
             ->assertJsonCount(0);

    }

    public function test_panel_fields_authorization_works(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             Panel::make('Panel', [
                                 EditableField::make('field-1')->canSee(fn() => false),
                                 EditableField::make('field-2')->canSee(fn() => true),
                             ])
                         );

        $this->resourceFieldsApi($resource)
             ->assertJsonCount(1)
             ->assertJsonCount(1, '0.value')
             ->assertJsonFragment([
                 'attribute' => 'field-2',
             ]);

    }

    public function test_default_values_are_returned_correctly(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             Panel::make('Personal Information', [
                                 EditableField::make('Name')->default('Default Value'),
                             ])
                         );

        $this->resourceFieldsApi($resource)
             ->assertJsonFragment([
                 'attribute' => 'name',
                 'value' => 'Default Value',
             ]);

    }

}
