<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature;

use DigitalCreative\Jaqen\Tests\Fixtures\Policies\AllowEverythingPolicy;
use DigitalCreative\Jaqen\Tests\Fixtures\Policies\ArticlePolicy;
use DigitalCreative\Jaqen\Tests\Fixtures\Policies\DisallowEverythingPolicy;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\Article as ArticleResource;
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

}
