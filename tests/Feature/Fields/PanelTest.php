<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\Panel;
use DigitalCreative\Dashboard\Http\Controllers\DetailController;
use DigitalCreative\Dashboard\Http\Controllers\IndexController;
use DigitalCreative\Dashboard\Http\Controllers\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\UpdateController;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

class PanelTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

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
        $user = factory(UserModel::class)->create([ 'name' => 'hello world' ]);

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             Panel::make('Personal Information', [
                                 EditableField::make('Name'),
                             ])
                         );

        /**
         * Index Controller
         */
        $indexRequest = $this->indexRequest($resource::uriKey());
        $indexResponse = $this->deepSerialize((new IndexController())->index($indexRequest));

        $this->assertEquals('panel', data_get($indexResponse, 'resources.0.fields.0.component'));
        $this->assertEquals('hello world', data_get($indexResponse, 'resources.0.fields.0.value.0.value'));

        /**
         * Detail Controller
         */
        $detailRequest = $this->detailRequest($resource::uriKey(), $user->getKey());
        $detailResponse = $this->deepSerialize((new DetailController())->detail($detailRequest));

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

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             Panel::make('Personal Information', [
                                 EditableField::make('Name')->rules('required'),
                                 EditableField::make('Email')->rules('required'),
                                 EditableField::make('Gender')->rules('required'),
                                 EditableField::make('Password')->rules('required'),
                             ])
                         );

        $request = $this->storeRequest($resource::uriKey(), $data);

        (new StoreController())->store($request);

        $this->assertDatabaseHas('users', $data);

    }

    public function test_on_store_the_value_if_inner_fields_are_proxied_correctly_on_update(): void
    {

        $user = factory(UserModel::class)->create();

        $data = [
            'name' => 'Hello World',
        ];

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             Panel::make('Personal Information', [
                                 EditableField::make('Name'),
                             ])
                         );

        $request = $this->updateRequest($resource::uriKey(), $user->getKey(), $data);

        (new UpdateController())->update($request);

        $this->assertDatabaseHas('users', $data);

    }

}
