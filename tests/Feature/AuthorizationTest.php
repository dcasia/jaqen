<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature;

use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
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

    public function test_unauthorized_user_can_not_call_any_resource(): void
    {

        $user = UserFactory::new()->create();

        $this->registerResource(UserResource::class, MinimalUserResource::class);
        $this->registerPolicy(UserResource::class, DisallowEverythingPolicy::class);

        $this->resourceIndexApi(UserResource::class)->assertForbidden();
        $this->resourceStoreApi(UserResource::class)->assertForbidden();
        $this->resourceShowApi(UserResource::class, key: $user->id)->assertForbidden();
        $this->resourceUpdateApi(UserResource::class, key: $user->id)->assertForbidden();
        $this->resourceDestroyApi(UserResource::class, keys: [ $user->id ])->assertForbidden();

        /**
         * Override the policy and try again...
         */
        $this->registerPolicy(MinimalUserResource::class, AllowEverythingPolicy::class);

        $this->resourceIndexApi(MinimalUserResource::class)->assertOk();
        $this->resourceStoreApi(MinimalUserResource::class)->assertCreated();
        $this->resourceShowApi(MinimalUserResource::class, key: $user->id)->assertOk();
        $this->resourceUpdateApi(MinimalUserResource::class, key: $user->id)->assertOk();
        $this->resourceDestroyApi(MinimalUserResource::class, keys: [ $user->id ])->assertNoContent();

    }

}
