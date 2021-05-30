<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature;

use DigitalCreative\Jaqen\Fields\Relationships\BelongsToField;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Jaqen\Tests\Fixtures\Policies\AllowEverythingPolicy;
use DigitalCreative\Jaqen\Tests\Fixtures\Policies\ArticlePolicy;
use DigitalCreative\Jaqen\Tests\Fixtures\Policies\DisallowEverythingPolicy;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\Article as ArticleResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\MinimalUserResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\User as UserResource;
use DigitalCreative\Jaqen\Tests\TestCase;

class AuthorizationTest extends TestCase
{

    public function test_resource_authorization_works(): void
    {

        $this->registerResource(UserResource::class, ArticleResource::class);

        $this->registerPolicy(UserResource::class, AllowEverythingPolicy::class);
        $this->registerPolicy(ArticleResource::class, DisallowEverythingPolicy::class);

        $this->resourcesApi()
             ->assertJsonCount(1)
             ->assertJson([
                 [
                     'name' => 'User',
                     'label' => 'Users',
                     'uriKey' => 'users',
                 ],
             ]);

    }

    public function test_policies_are_applied_on_all_crud_operations(): void
    {

        $user = UserFactory::new()->create();

        $this->registerResource(MinimalUserResource::class);
        $this->registerPolicy(MinimalUserResource::class, DisallowEverythingPolicy::class);

        $this->resourceIndexApi(MinimalUserResource::class)->assertForbidden();
        $this->resourceCreateApi(MinimalUserResource::class)->assertForbidden();
        $this->resourceDetailApi(MinimalUserResource::class, key: $user->id)->assertForbidden();
        $this->resourceUpdateApi(MinimalUserResource::class, key: $user->id)->assertForbidden();
        $this->resourceDeleteApi(MinimalUserResource::class, keys: [ $user->id ])->assertForbidden();

        /**
         * Override the policy and try again...
         */
        $this->registerPolicy(MinimalUserResource::class, AllowEverythingPolicy::class);

        $this->resourceIndexApi(MinimalUserResource::class)->assertOk();
        $this->resourceCreateApi(MinimalUserResource::class)->assertCreated();
        $this->resourceDetailApi(MinimalUserResource::class, key: $user->id)->assertOk();
        $this->resourceUpdateApi(MinimalUserResource::class, key: $user->id)->assertOk();
        $this->resourceDeleteApi(MinimalUserResource::class, keys: [ $user->id ])->assertNoContent();

    }

    public function test_fields_and_filters_can_not_be_retrieved_if_policy_denies(): void
    {

        $this->registerResource(UserResource::class);
        $this->registerPolicy(UserResource::class, DisallowEverythingPolicy::class);

        $this->resourceFieldsApi(UserResource::class)->assertForbidden();
        $this->resourceFiltersApi(UserResource::class)->assertForbidden();

        /**
         * Override the policy and try again...
         */
        $this->registerPolicy(UserResource::class, AllowEverythingPolicy::class);

        $this->resourceFieldsApi(UserResource::class)->assertOk();
        $this->resourceFiltersApi(UserResource::class)->assertOk();

    }

    public function test_belongs_to_search_api_respect_authorization(): void
    {

        $resource = $this->makeResource(ArticleModel::class);

        $field = BelongsToField::make('User')->searchable()->setRelatedResource(MinimalUserResource::class);

        $resource->addDefaultFields($field);

        $this->registerPolicy($resource, DisallowEverythingPolicy::class);
        $this->belongsToSearchApi($resource, $field)->assertForbidden();

        /**
         * Override the policy and try again...
         */
        $this->registerPolicy($resource, AllowEverythingPolicy::class);
        $this->belongsToSearchApi($resource, $field)->assertOk();

    }

}
