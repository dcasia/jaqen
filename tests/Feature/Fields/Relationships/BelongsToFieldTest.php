<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields\Relationships;

use DigitalCreative\Jaqen\Fields\Relationships\BelongsToField;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Repository\Repository;
use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Services\Fields\Fields\ReadOnlyField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Factories\ArticleFactory;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\MinimalUserResource;
use DigitalCreative\Jaqen\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mockery\MockInterface;

class BelongsToFieldTest extends TestCase
{

    public function test_index_listing_works(): void
    {

        $article = ArticleFactory::new()->create();

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')
                                           ->setRelatedResource(MinimalUserResource::class)
                         );

        $this->resourceIndexApi($resource)
             ->assertJson([
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

            public function newModel(): Model
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

        $attribute = $this->resourceIndexApi($resource)
                          ->json('resources.0.fields.0.relatedResource.fields.0.attribute');

        $this->assertEquals('custom_field_name', $attribute);

    }

    public function test_edit_works(): void
    {

        $article = ArticleFactory::new()->create();
        $user = UserFactory::new()->create();

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(BelongsToField::make('User'));

        $this->resourceUpdateApi($resource, key: $article->id, data: [ 'user_id' => $user->id ])
             ->assertOk();

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

        $this->resourceStoreApi($resource, $data);

        $this->assertDatabaseHas('articles', $data);

    }

    public function test_searchable_belongs_to_field_works(): void
    {

        ArticleFactory::new()->create();

        $user = UserFactory::new()->create([ 'name' => 'random' ]);

        UserFactory::new()->count(5)->create();

        $resource = $this->makeResource(ArticleModel::class);

        $field = BelongsToField::make('User')
                               ->setRelatedResource(MinimalUserResource::class)
                               ->searchable(function (Builder $builder, BaseRequest $request): Builder {

                                   $search = $request->query('search');

                                   $this->assertSame('random', $search);

                                   return $builder->where('name', 'like', "%$search%");

                               });

        $resource->addDefaultFields($field);

        $this->belongsToSearchApi($resource, $field, [ 'search' => 'random' ])
             ->assertJsonCount(1)
             ->assertJson([
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
             ]);

    }

    public function test_it_returns_result_using_the_default_search_logic(): void
    {

        ArticleFactory::new()->create();

        $user = UserFactory::new()->create();
        $resource = $this->makeResource(ArticleModel::class);

        $field = BelongsToField::make('User')
                               ->searchable()
                               ->setRelatedResource(MinimalUserResource::class);

        $resource->addDefaultFields($field);

        $this->belongsToSearchApi($resource, $field, [ 'id' => $user->id ])
             ->assertJson([
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
             ]);

    }

    public function test_non_searchable_field_cannot_be_searched(): void
    {

        ArticleFactory::new()->create();

        $user = UserFactory::new()->create();
        $field = BelongsToField::make('User');

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields($field);

        $this->belongsToSearchApi($resource, $field, [ 'id' => $user->id ])
             ->assertNotFound();

    }

    public function test_related_model_can_be_null(): void
    {

        $article = ArticleFactory::new()->create([ 'user_id' => null ]);

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')
                                           ->setRelatedResource(MinimalUserResource::class),
                         );

        $this->resourceShowApi($resource, $article->id)
             ->assertJsonPath('fields.0.attribute', 'user_id')
             ->assertJsonPath('fields.0.value', null);

    }

    public function test_eager_loading_does_not_override_user_definitions(): void
    {

        $article = ArticleFactory::new()->create();

        /**
         * User defined relationships shouldn't be replaced
         */
        $with = [
            'demo',
            'user' => fn(Builder $builder) => $builder,
        ];

        $repository = $this->mock(Repository::class, function (MockInterface $mock) use ($article, $with) {
            $mock->shouldReceive('findByKey')->with($article->id, $with)->andReturn($article);
        });

        $resource = $this->makeResource(ArticleModel::class)
                         ->useRepository($repository)
                         ->with($with)
                         ->addDefaultFields(BelongsToField::make('User'));

        $this->resourceShowApi($resource, $article->id)->assertOk();

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

        $this->resourceShowApi($resource, $article->id)->assertOk();

    }

    public function test_it_works_on_fields_request_call(): void
    {

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             BelongsToField::make('User')->setRelatedResource(MinimalUserResource::class),
                         );

        $this->resourceFieldsApi($resource)
             ->assertJson([
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
             ]);

    }

}
