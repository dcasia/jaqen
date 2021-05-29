<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields\Relationships;

use DigitalCreative\Jaqen\Fields\Relationships\BelongsToField;
use DigitalCreative\Jaqen\Http\Controllers\Relationships\BelongsToController;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Repository\Repository;
use DigitalCreative\Jaqen\Services\Fields\EditableField;
use DigitalCreative\Jaqen\Services\Fields\ReadOnlyField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Factories\ArticleFactory;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\MinimalUserResource;
use DigitalCreative\Jaqen\Tests\TestCase;
use DigitalCreative\Jaqen\Tests\Traits\RelationshipRequestTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BelongsToFieldTest extends TestCase
{

    use RelationshipRequestTrait;

    public function test_index_listing_works(): void
    {

        $article = ArticleFactory::new()->create();

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')
                                           ->setRelatedResource(MinimalUserResource::class)
                         );

        $response = $this->indexResponse($resource);

        $this->assertEquals($response, [
            'total' => 1,
            'from' => 1,
            'to' => 1,
            'currentPage' => 1,
            'lastPage' => 1,
            'resources' => [
                [
                    'key' => $article->id,
                    'fields' => [
                        [
                            'label' => 'User',
                            'attribute' => 'user_id',
                            'value' => $article->user->id,
                            'component' => 'belongs-to-field',
                            'additionalInformation' => null,
                            'searchable' => false,
                            'relatedResource' => [
                                'name' => 'Minimal User Resource',
                                'label' => 'Minimal User Resources',
                                'uriKey' => 'minimal-user-resources',
                                'fields' => [
                                    [
                                        'label' => 'Name',
                                        'attribute' => 'name',
                                        'value' => $article->user->name,
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

    public function test_fields_for_works_correctly_on_related_resource(): void
    {

        ArticleFactory::new()->create();

        $class = new class() extends AbstractResource {

            public function model(): Model
            {
                return new ArticleModel();
            }

            public function fieldsForTest(): array
            {
                return [
                    new ReadOnlyField('Custom Field Name'),
                ];
            }

        };

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')
                                           ->setRelatedResource(get_class($class))
                                           ->setRelatedResourceFieldsFor('test'),
                         );

        $response = $this->indexResponse($resource);

        $this->assertEquals(
            'custom_field_name', data_get($response, 'resources.0.fields.0.relatedResource.fields.0.attribute')
        );

    }

    public function test_edit_works(): void
    {

        $article = ArticleFactory::new()->create();
        $user = UserFactory::new()->create();

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(BelongsToField::make('User'));

        $response = $this->updateResponse($resource, $article->id, [ 'user_id' => $user->id ]);

        $this->assertTrue($response);

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

        $this->storeResponse($resource, $data);

        $this->assertDatabaseHas('articles', $data);

    }

    /**
     * Searchable
     */
    public function test_searchable_belongs_to_field_works(): void
    {

        ArticleFactory::new()->create();

        $user = UserFactory::new()->create([ 'name' => 'random' ]);

        $resource = $this->makeResource(ArticleModel::class);

        $field = BelongsToField::make('User')
                               ->setRelatedResource(MinimalUserResource::class)
                               ->searchable(function (Builder $builder, BaseRequest $request): Builder {

                                   $search = $request->query('search');

                                   $this->assertSame('random', $search);

                                   return $builder->where('name', 'like', "%$search%");

                               });

        $request = $this->belongsToSearchRequest($resource, $field, [ 'search' => 'random' ]);

        $resource->addDefaultFields($field);

        $response = (new BelongsToController())->searchBelongsTo($request);

        $this->assertSame([
            [
                'key' => $user->id,
                'fields' => [
                    [
                        'label' => 'Name',
                        'attribute' => 'name',
                        'value' => $user->name,
                        'component' => 'editable-field',
                        'additionalInformation' => null,
                    ],
                ],
            ],
        ], $response->getData(true));

    }

    public function test_it_returns_result_using_the_default_search_logic(): void
    {

        ArticleFactory::new()->create();

        $user = UserFactory::new()->create();
        $resource = $this->makeResource(ArticleModel::class);

        $field = BelongsToField::make('User')
                               ->searchable()
                               ->setRelatedResource(MinimalUserResource::class);

        $request = $this->belongsToSearchRequest($resource, $field, [ 'id' => $user->id ]);

        $resource->addDefaultFields($field);

        $response = (new BelongsToController())->searchBelongsTo($request);

        $this->assertSame([
            [
                'key' => $user->id,
                'fields' => [
                    [
                        'label' => 'Name',
                        'attribute' => 'name',
                        'value' => $user->name,
                        'component' => 'editable-field',
                        'additionalInformation' => null,
                    ],
                ],
            ],
        ], $response->getData(true));

    }

    public function test_non_searchable_field_cannot_be_searched(): void
    {

        ArticleFactory::new()->create();

        $user = UserFactory::new()->create();
        $field = BelongsToField::make('User');

        $this->expectException(NotFoundHttpException::class);

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields($field);

        $request = $this->belongsToSearchRequest($resource, $field, [ 'id' => $user->id ]);

        (new BelongsToController())->searchBelongsTo($request);

    }

    public function test_related_model_can_be_null(): void
    {

        $article = ArticleFactory::new()->create([ 'user_id' => null ]);

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')
                                           ->setRelatedResource(MinimalUserResource::class),
                         );

        $response = $this->detailResponse($resource, $article->id);

        $this->assertIsArray($response);

    }

    public function test_eager_loading_does_not_override_user_definitions(): void
    {

        $article = ArticleFactory::new()->create();

        /**
         * User defined relationships shouldn't be replaced
         */
        $with = [
            'demo', 'user' => fn(Builder $builder) => $builder,
        ];

        $repository = $this->mock(Repository::class, function (MockInterface $mock) use ($article, $with) {
            $mock->shouldReceive('findByKey')->with($article->id, $with)->andReturn($article);
        });

        $resource = $this->makeResource(ArticleModel::class)
                         ->useRepository($repository)
                         ->with($with)
                         ->addDefaultFields(BelongsToField::make('User'));

        $this->detailResponse($resource, $article->id);

    }

    public function test_eager_relation_is_injected_into_the_resource(): void
    {

        $article = ArticleFactory::new()->create();

        $with = [ 'demo' ];

        $repository = $this->mock(Repository::class, function (MockInterface $mock) use ($article, $with) {
            $mock->shouldReceive('findByKey')
                 ->with($article->id, array_merge($with, [ 'user' ]))
                 ->andReturn($article);
        });

        $resource = $this->makeResource(ArticleModel::class)
                         ->useRepository($repository)
                         ->with($with)
                         ->addDefaultFields(BelongsToField::make('User'));

        $this->detailResponse($resource, $article->id);

    }

    public function test_it_works_on_fields_request_call(): void
    {

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')->setRelatedResource(MinimalUserResource::class),
                         );

        $response = $this->fieldsResponse($resource);

        $this->assertEquals([
            [
                'label' => 'User',
                'attribute' => 'user_id',
                'value' => null,
                'component' => 'belongs-to-field',
                'additionalInformation' => null,
                'searchable' => false,
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
        ], $response);

    }

}
