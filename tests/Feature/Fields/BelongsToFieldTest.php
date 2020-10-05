<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Http\Controllers\DetailController;
use DigitalCreative\Dashboard\Http\Controllers\IndexController;
use DigitalCreative\Dashboard\Http\Controllers\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\UpdateController;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Tests\Factories\ArticleFactory;
use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
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

        $article = ArticleFactory::new()->create();

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')
                                           ->setRelatedResource(MinimalUserResource::class)
                                           ->withExtraRelatedResourceData(fn(BaseRequest $request, UserModel $user) => [ 'name' => $user->name ]),
                         );

        $request = $this->indexRequest($resource::uriKey());

        $response = (new IndexController())->index($request);

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
                                'name' => $article->user->name,
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
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    }

    public function test_edit_works(): void
    {

        $article = ArticleFactory::new()->create();
        $user = UserFactory::new()->create();

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(BelongsToField::make('User'));

        $request = $this->updateRequest($resource::uriKey(), $article->id, [ 'user_id' => $user->id ]);

        $this->assertTrue((new UpdateController())->update($request));

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'user_id' => $user->id,
        ]);

    }

    public function test_create_works(): void
    {

        $user = UserFactory::new()->create();

        $data = [
            'title' => 'Hello',
            'content' => 'world',
            'user_id' => $user->id,
        ];

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             EditableField::make('Title'),
                             EditableField::make('Content'),
                             BelongsToField::make('User'),
                         );

        $request = $this->storeRequest($resource::uriKey(), $data);

        (new StoreController())->store($request);

        $this->assertDatabaseHas('articles', $data);

    }

    public function test_user_is_able_to_pass_options(): void
    {

        $article = ArticleFactory::new()->create();

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')->options(function(BaseRequest $request) {
                                 return [
                                     [ 'id' => 1 ],
                                 ];
                             }),
                         );

        $request = $this->detailRequest($resource::uriKey(), $article->id);

        $response = (new DetailController())->detail($request);

        $this->assertSame($response, [
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
                        ],
                    ],
                ],
            ],
        ]);

    }

    /**
     * Searchable
     */
    public function test_searchable_belongs_to_field_works(): void
    {

        $article = ArticleFactory::new()->create();

        UserFactory::new()->create([ 'name' => 'random' ]);

        $request = $this->belongsToRequest(ArticleResource::uriKey(), $article->id, 'user', [ 'search' => 'random' ]);

        $response = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')
                                           ->setRelatedResource(MinimalUserResource::class)
                                           ->searchable(function(Builder $builder, BaseRequest $request) {

                                               $search = $request->query('search');

                                               $this->assertSame('random', $search);

                                               return $builder->where('name', 'like', "%$search%");

                                           }),
                         )
                         ->searchBelongsToRelation($request);

        $this->assertSame($this->deepSerialize($response), [ [ 'name' => 'random' ] ]);

    }

    public function test_it_returns_result_using_the_default_search_logic(): void
    {

        $article = ArticleFactory::new()->create();
        $user = UserFactory::new()->create();

        $request = $this->belongsToRequest(ArticleResource::uriKey(), $article->id, 'user', [ 'id' => $user->id ]);

        $response = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')
                                           ->searchable()
                                           ->setRelatedResource(MinimalUserResource::class),
                         )
                         ->searchBelongsToRelation($request);

        $this->assertSame($this->deepSerialize($response), [ [ 'name' => $user->name ] ]);

    }

    public function test_non_searchable_field_cannot_be_searched(): void
    {

        $article = ArticleFactory::new()->create();
        $user = UserFactory::new()->create();

        $request = $this->belongsToRequest(ArticleResource::uriKey(), $article->id, 'user', [ 'id' => $user->id ]);

        $this->expectException(NotFoundHttpException::class);

        $this->makeResource(ArticleModel::class)
             ->addDefaultFields(BelongsToField::make('User'),)
             ->searchBelongsToRelation($request);

    }

}
