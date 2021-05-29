<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields\Relationships;

use DigitalCreative\Jaqen\Fields\Relationships\HasOneField;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\MinimalUserResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\PhoneResource;
use DigitalCreative\Jaqen\Tests\TestCase;
use DigitalCreative\Jaqen\Tests\Traits\RelationshipRequestTrait;
use Illuminate\Validation\ValidationException;

class HasOneFieldTest extends TestCase
{

    use RelationshipRequestTrait;

    public function test_it_create_related_resource_correctly(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             HasOneField::make('Phone')->setRelatedResource(PhoneResource::class),
                         );

        $this->resourceStoreApi($resource, [ 'phone' => [ 'number' => 123456 ] ])
             ->assertStatus(201)
             ->assertJson([
                 'id' => 1,
                 'phone' => [
                     'id' => 1,
                     'number' => 123456,
                     'user_id' => 1,
                 ],
             ]);

    }

    public function test_it_works_on_index(): void
    {

        $user = UserFactory::new()->count(2)->withPhone()->create()->first();

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             HasOneField::make('Phone')->setRelatedResource(PhoneResource::class),
                         );

        $this->resourceIndexApi($resource)
             ->assertStatus(200)
             ->assertJsonPath('resources.0.key', $user->id)
             ->assertJsonPath('resources.0.fields.0.value', $user->phone->id)
             ->assertJsonPath('resources.0.fields.0.relatedResource.fields.0.value', $user->phone->number);

    }

    public function test_index_listing_works_when_related_resource_is_null(): void
    {

        $user = UserFactory::new()->count(2)->create()->first();

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             HasOneField::make('Phone')->setRelatedResource(PhoneResource::class),
                         );

        $this->resourceIndexApi($resource)
             ->assertJsonPath('resources.0.key', $user->id)
             ->assertJsonPath('resources.0.fields.0.value', null)
             ->assertJsonPath('resources.0.fields.0.relatedResource.fields.0.value', null);

    }

    public function test_it_works_on_fields_request_call(): void
    {

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             HasOneField::make('User')->setRelatedResource(MinimalUserResource::class),
                         );

        $this->resourceFieldsApi($resource)
             ->assertStatus(200)
             ->assertJson([
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
             ]);

    }

    public function test_validation_works(): void
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             HasOneField::make('Phone')
                                        ->setRelatedResource(PhoneResource::class, 'fieldsWithValidation'),
                         );

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $this->resourceStoreApi($resource, [ 'phone' => [ 'number' => 'abc' ] ])
             ->assertStatus(422);

    }

}
