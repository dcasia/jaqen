<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields\Relationships;

use DigitalCreative\Jaqen\Fields\Relationships\HasOneField;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\MinimalUserResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\PhoneResource;
use DigitalCreative\Jaqen\Tests\TestCase;
use DigitalCreative\Jaqen\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Jaqen\Tests\Traits\RelationshipRequestTrait;
use DigitalCreative\Jaqen\Tests\Traits\RequestTrait;
use DigitalCreative\Jaqen\Tests\Traits\ResourceTrait;
use Illuminate\Validation\ValidationException;

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

        $response = $this->storeResponse($resource, [ 'phone' => [ 'number' => 123456 ] ]);

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

        $response = $this->indexResponse($resource);

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

        $response = $this->indexResponse($resource);

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

        $response = $this->fieldsResponse($resource);

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

    public function test_validation_works(): void
    {

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             HasOneField::make('Phone')
                                        ->setRelatedResource(PhoneResource::class, 'fieldsWithValidation'),
                         );

        $this->expectException(ValidationException::class);

        $this->storeResponse($resource, [ 'phone' => [ 'number' => 'abc' ] ]);

    }

}
