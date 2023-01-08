<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers\Resources;

use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Tests\Factories\ArticleFactory;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\UserResource;
use DigitalCreative\Jaqen\Tests\TestCase;

class UpdateControllerTest extends TestCase
{
    public function test_resource_update(): void
    {
        $user = UserFactory::new()->create();

        $data = [
            'name' => 'Demo',
            'email' => 'email@email.com',
        ];

        $this->registerResource(UserResource::class);
        $this->resourceUpdateApi(UserResource::class, key: $user->id, data: $data)
            ->assertOk();

        $this->assertDatabaseHas(User::class, $data);
    }

    public function test_read_only_fields_does_not_get_update(): void
    {
        $user = UserFactory::new()->create();

        $this->registerResource(UserResource::class);
        $this->resourceUpdateApi(UserResource::class, key: $user->id, data: [ 'id' => 2 ])
            ->assertOk();

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function test_field_is_not_required_when_sometimes_rules_is_applied_during_update(): void
    {
        $article = ArticleFactory::new()->create();

        $resource = $this->makeResource(ArticleModel::class)->addDefaultFields(
            EditableField::make('title')->rulesForUpdate([ 'sometimes', 'required' ]),
            EditableField::make('content')->rulesForUpdate([ 'sometimes', 'required' ]),
        );

        $data = [
            'title' => 'Avoid updating the content intentionally, as it has `sometimes` rules.',
        ];

        $this->resourceUpdateApi($resource, key: $article->id, data: $data)
            ->assertOk();

        /**
         * Try to update again but now sending a content key with null value
         */
        $this->resourceUpdateApi($resource, key: $article->id, data: $data + [ 'content' => null ])
            ->assertUnprocessable()
            ->assertJsonFragment([
                'errors' => [
                    'content' => [
                        'The content field is required.',
                    ],
                ],
            ]);
    }
}
