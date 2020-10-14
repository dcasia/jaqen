<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Fields\HasOneField;
use DigitalCreative\Dashboard\Fields\ReadOnlyField;
use DigitalCreative\Dashboard\Http\Controllers\FieldsController;
use DigitalCreative\Dashboard\Http\Controllers\Relationships\BelongsToController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\DetailController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\IndexController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\UpdateController;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Repository\Repository;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Tests\Factories\ArticleFactory;
use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Article;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\MinimalUserResource;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\PhoneResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RelationshipRequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HasOneFieldTest extends TestCase
{

    use RequestTrait;
    use RelationshipRequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

    public function test_it_create_related_resource_correctly(): void
    {

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(
                             HasOneField::make('Phone')
                                        ->setRelatedResource(PhoneResource::class)
                                        ->setRelatedResourceFieldsFor('creation'),
                         );

        $request = $this->storeRequest($resource, [ 'phone' => [ 'number' => 123456 ] ]);

        $response = (new StoreController())->handle($request)->getData(true);

        $this->markTestIncomplete('todo');

    }

    public function test_it_works_on_fields_request_call(): void
    {

        $resource = $this->makeResource(ArticleModel::class)
                         ->addDefaultFields(
                             HasOneField::make('User')->setRelatedResource(MinimalUserResource::class),
                         );

        $request = $this->fieldsRequest($resource);

        $response = (new FieldsController())->fields($request)->getData(true);

        $this->assertEquals([
            [
                'label' => 'User',
                'attribute' => 'user',
                'value' => null,
                'component' => 'has-one-field',
                'additionalInformation' => null,
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
