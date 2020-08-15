<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\Article as ArticleResource;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\MinimalUserResource;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\User as UserResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

class BelongsToFieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

    public function test_index_listing_works(): void
    {

        /**
         * @var ArticleModel $article
         */
        $article = factory(ArticleModel::class)->create();

        $request = $this->indexRequest(ArticleResource::uriKey());

        $response = $this->makeResource($request, ArticleModel::class)
                         ->addFields(
                             BelongsToField::make('User', 'user', MinimalUserResource::class)
                                           ->withExtraRelationData(fn(BaseRequest $request, UserModel $user) => [ 'name' => $user->name ]),
                         )
                         ->index();

        $this->assertSame($this->deepSerialize($response), [
            'total' => 1,
            'resources' => [
                [
                    'key' => $article->id,
                    'fields' => [
                        [
                            'label' => 'User',
                            'attribute' => 'user_id',
                            'value' => [
                                'belongsToId' => $article->user->id,
                                'blueprint' => [
                                    [
                                        'label' => 'Name',
                                        'attribute' => 'name',
                                        'value' => null,
                                        'component' => 'editable-field',
                                        'additionalInformation' => null,
                                    ]
                                ]
                            ],
                            'component' => 'belongs-to-field',
                            'additionalInformation' => [
                                'name' => $article->user->name
                            ],
                        ]
                    ]
                ]
            ]
        ]);

    }

    public function test_edit_works(): void
    {

        /**
         * @var ArticleModel $article
         * @var UserModel $user
         */
        $article = factory(ArticleModel::class)->create();
        $user = factory(UserModel::class)->create();

        $request = $this->updateRequest(ArticleResource::uriKey(), $article->id, [ 'user_id' => $user->id ]);

        $response = $this->makeResource($request, ArticleModel::class)
                         ->addFields(BelongsToField::make('User'))
                         ->update();

        $this->assertTrue($response);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'user_id' => $user->id,
        ]);

    }

    public function test_create_works(): void
    {

        /**
         * @var UserModel $user
         */
        $user = factory(UserModel::class)->create();

        $data = [
            'title' => 'Hello',
            'content' => 'world',
            'user_id' => $user->id
        ];

        $request = $this->createRequest(ArticleResource::uriKey(), $data);

        $this->makeResource($request, ArticleModel::class)
             ->addFields(
                 EditableField::make('Title'),
                 EditableField::make('Content'),
                 BelongsToField::make('User', 'user', UserResource::class),
             )
             ->create();

        $this->assertDatabaseHas('articles', $data);

    }

}
