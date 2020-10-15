<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\Relationships\BelongsToManyField;
use DigitalCreative\Dashboard\Http\Controllers\FieldsController;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\PhoneResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RelationshipRequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

//class BelongsToManyTest extends TestCase
//{
//
//    use RequestTrait;
//    use RelationshipRequestTrait;
//    use ResourceTrait;
//    use InteractionWithResponseTrait;
//
//    public function test_it_create_related_resource_correctly(): void
//    {
//
//        $resource = $this->makeResource(UserModel::class)
//                         ->addDefaultFields(
//                             BelongsToManyField::make('Phone')
//                                               ->setRelatedResource(PhoneResource::class),
//                         );
//
//        $request = $this->fieldsRequest($resource);
//
//        $response = (new FieldsController())->fields($request)->getData(true);
//
//        dd($response);
//
//    }
//
//}
