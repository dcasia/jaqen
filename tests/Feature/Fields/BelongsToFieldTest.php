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
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
                             BelongsToField::make('User')
                                           ->setRelatedResource(MinimalUserResource::class)
                                           ->withExtraRelatedResourceData(fn(BaseRequest $request, UserModel $user) => [ 'name' => $user->name ]),
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
                            'value' => $article->user->id,
                            'component' => 'belongs-to-field',
                            'additionalInformation' => [
                                'name' => $article->user->name
                            ],
                            'settings' => [
                                'searchable' => false,
                                'options' => null,
                                'fields' => [
                                    [
                                        'label' => 'Name',
                                        'attribute' => 'name',
                                        'value' => null,
                                        'component' => 'editable-field',
                                        'additionalInformation' => null,
                                    ]
                                ],
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

        $request = $this->storeRequest(ArticleResource::uriKey(), $data);

        $this->makeResource($request, ArticleModel::class)
             ->addFields(
                 EditableField::make('Title'),
                 EditableField::make('Content'),
                 BelongsToField::make('User'),
             )
             ->store();

        $this->assertDatabaseHas('articles', $data);

    }

    public function test_user_is_able_to_pass_options(): void
    {

        /**
         * @var ArticleModel $article
         */
        $article = factory(ArticleModel::class)->create();

        $request = $this->detailRequest(ArticleResource::uriKey(), $article->id);

        $response = $this->makeResource($request, ArticleModel::class)
                         ->addFields(
                             BelongsToField::make('User')->options(static function (BaseRequest $request) {
                                 return [
                                     [ 'id' => 1 ],
                                 ];
                             }),
                         )
                         ->detail();

        $this->assertSame($this->deepSerialize($response), [
            'key' => $article->id,
            'fields' => [
                [
                    'label' => 'User',
                    'attribute' => 'user_id',
                    'value' => $article->user->id,
                    'component' => 'belongs-to-field',
                    'additionalInformation' => null,
                    'settings' => [
                        'searchable' => false,
                        'options' => [
                            [ 'id' => 1 ],
                        ]
                    ],
                ]
            ]
        ]);

    }

    /**
     * Searchable
     */
    public function test_searchable_belongs_to_field_works(): void
    {

        /**
         * @var ArticleModel $article
         */
        $article = factory(ArticleModel::class)->create();

        factory(UserModel::class)->create([ 'name' => 'random' ]);

        $request = $this->belongsToRequest(ArticleResource::uriKey(), $article->id, 'user', [ 'search' => 'random' ]);

        $response = $this->makeResource($request, ArticleModel::class)
                         ->addFields(
                             BelongsToField::make('User')
                                           ->setRelatedResource(MinimalUserResource::class)
                                           ->searchable(function (Builder $builder, BaseRequest $request) {

                                               $search = $request->query('search');

                                               $this->assertSame('random', $search);

                                               return $builder->where('name', 'like', "%$search%");

                                           }),
                         )
                         ->searchBelongsToRelation();

        $this->assertSame($this->deepSerialize($response), [ [ 'name' => 'random' ] ]);

    }

    public function test_it_returns_result_using_the_default_search_logic(): void
    {

        /**
         * @var ArticleModel $article
         * @var UserModel $user
         */
        $article = factory(ArticleModel::class)->create();
        $user = factory(UserModel::class)->create();

        $request = $this->belongsToRequest(ArticleResource::uriKey(), $article->id, 'user', [ 'id' => $user->id ]);

        $response = $this->makeResource($request, ArticleModel::class)
                         ->addFields(
                             BelongsToField::make('User')
                                           ->searchable()
                                           ->setRelatedResource(MinimalUserResource::class),
                         )
                         ->searchBelongsToRelation();

        $this->assertSame($this->deepSerialize($response), [ [ 'name' => $user->name ] ]);

    }

    public function test_non_searchable_field_cannot_be_searched(): void
    {

        /**
         * @var ArticleModel $article
         * @var UserModel $user
         */
        $article = factory(ArticleModel::class)->create();
        $user = factory(UserModel::class)->create();

        $request = $this->belongsToRequest(ArticleResource::uriKey(), $article->id, 'user', [ 'id' => $user->id ]);

        $this->expectException(NotFoundHttpException::class);

        $this->makeResource($request, ArticleModel::class)
             ->addFields(BelongsToField::make('User'),)
             ->searchBelongsToRelation();

    }

}
