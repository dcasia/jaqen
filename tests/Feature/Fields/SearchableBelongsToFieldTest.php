<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\SearchableBelongsToField;
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

class SearchableBelongsToFieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

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
                             SearchableBelongsToField::make('User', 'user', MinimalUserResource::class)
                                                     ->onSearch(function (Builder $builder, BaseRequest $request) {

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
                         ->addFields(SearchableBelongsToField::make('User', 'user', MinimalUserResource::class),)
                         ->searchBelongsToRelation();

        $this->assertSame($this->deepSerialize($response), [ [ 'name' => $user->name ] ]);

    }

}
