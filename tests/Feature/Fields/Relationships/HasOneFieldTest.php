<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields\Relationships;

use DigitalCreative\Dashboard\Fields\Relationships\HasOneField;
use DigitalCreative\Dashboard\Http\Controllers\FieldsController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\IndexController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\StoreController;
use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\MinimalUserResource;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\PhoneResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RelationshipRequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

class HasOneFieldTest extends TestCase
{

    use RequestTrait;
    use RelationshipRequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

    public function test_it_create_related_resource_correctly(): void
    {

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             HasOneField::make('Phone')->setRelatedResource(PhoneResource::class),
                         );

        $request = $this->storeRequest($resource, [ 'phone' => [ 'number' => 123456 ] ]);

        $response = (new StoreController())->handle($request)->getData(true);

        $this->assertEquals(123456, data_get($response, 'phone.number'));
        $this->assertEquals(1, data_get($response, 'phone.user_id'));
        $this->assertEquals(1, data_get($response, 'phone.id'));
        $this->assertEquals(1, data_get($response, 'id'));

    }

    public function test_it_works_on_index(): void
    {

        $user = UserFactory::new()->count(2)->withPhone()->create()->first();

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             HasOneField::make('Phone')->setRelatedResource(PhoneResource::class),
                         );

        $request = $this->indexRequest($resource);

        $response = (new IndexController())->handle($request)->getData(true);

        $this->assertEquals($user->id, data_get($response, 'resources.0.key'));
        $this->assertEquals($user->phone->id, data_get($response, 'resources.0.fields.0.value'));
        $this->assertEquals($user->phone->number, data_get($response, 'resources.0.fields.0.relatedResource.fields.0.value'));

    }

    public function test_index_listing_works_when_related_resource_is_null(): void
    {

        $user = UserFactory::new()->count(2)->create()->first();

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             HasOneField::make('Phone')->setRelatedResource(PhoneResource::class),
                         );

        $request = $this->indexRequest($resource);

        $response = (new IndexController())->handle($request)->getData(true);

        $this->assertEquals($user->id, data_get($response, 'resources.0.key'));
        $this->assertEquals(null, data_get($response, 'resources.0.fields.0.value'));
        $this->assertEquals(null, data_get($response, 'resources.0.fields.0.relatedResource.fields.0.value'));

    }

    public function test_it_works_on_fields_request_call(): void
    {

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             HasOneField::make('User')->setRelatedResource(MinimalUserResource::class),
                         );

        $request = $this->fieldsRequest($resource);

        $response = (new FieldsController())->fields($request)->getData(true);

        $this->assertEquals([
            [
                'label' => 'User',
                'attribute' => 'user',
                'value' => null,
                'component' => 'has-one-field',
                'additionalInformation' => null,
                'relatedResource' => [
                    'name' => 'Minimal User Resource',
                    'label' => 'Minimal User Resources',
                    'uriKey' => 'minimal-user-resources',
                    'fields' => [
                        [
                            'label' => 'Name',
                            'attribute' => 'name',
                            'value' => null,
                            'component' => 'editable-field',
                            'additionalInformation' => null,
                        ],
                    ],
                ],
            ],
        ], $response);

    }

}
