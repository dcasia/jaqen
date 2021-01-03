<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers\Resources;

use DigitalCreative\Jaqen\Services\Fields\EditableField;
use DigitalCreative\Jaqen\Tests\Factories\ArticleFactory;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Jaqen\Tests\TestCase;
use DigitalCreative\Jaqen\Tests\Traits\RequestTrait;
use DigitalCreative\Jaqen\Tests\Traits\ResourceTrait;

class UpdateControllerTest extends TestCase
{

    use ResourceTrait;
    use RequestTrait;

    public function test_resource_update(): void
    {

        UserFactory::new()->create();

        $data = [
            'name' => 'Demo',
            'email' => 'email@email.com',
        ];

        $this->patchJson('/jaqen-api/resource/users/1', $data)
             ->assertStatus(200);

        $this->assertDatabaseHas('users', $data);

    }

    public function test_read_only_fields_does_not_get_update(): void
    {

        $user = UserFactory::new()->create();

        $this->patchJson('/jaqen-api/resource/users/1', [ 'id' => 2 ])
             ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => 1,
            'name' => $user->name,
            'email' => $user->email,
        ]);

    }

    public function test_field_is_not_required_when_sometimes_rules_is_applied_during_update(): void
    {

        $article = ArticleFactory::new()->create();

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             EditableField::make('title')->rulesForUpdate([ 'sometimes', 'required' ]),
                             EditableField::make('content')->rulesForUpdate([ 'sometimes', 'required' ])
                         );

        $data = [
            'title' => 'Avoid updating the content intentionally, as it has `sometimes` rules.',
        ];

        $this->callUpdate($resource, $article, $data)->assertStatus(200);

        /**
         * Try to update again but now sending a content key with null value
         */
        $this->callUpdate($resource, $article, $data + [ 'content' => null ])
             ->assertStatus(422)
             ->assertJsonFragment([
                 'errors' => [
                     'content' => [
                         'The content field is required.',
                     ],
                 ],
             ]);

    }

}
