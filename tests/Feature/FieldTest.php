<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature;

use DigitalCreative\Dashboard\AbstractResource;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\StoreResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Resources\User as UserResource;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\InteractionWithResponseTrait;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class FieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;
    use InteractionWithResponseTrait;

    public function test_field_validation_on_create_works(): void
    {

        $request = $this->makeRequest('/', 'POST', [ 'name' => null ], StoreResourceRequest::class);

        $this->expectException(ValidationException::class);

        $this->getResource($request)
             ->addFields(
                 (new EditableField('name'))->rulesForCreate('required')
             )
             ->store();

    }

    public function test_field_validation_on_update_works(): void
    {

        $request = $this->makeRequest('/', 'POST', [ 'name' => null ], UpdateResourceRequest::class);

        $this->expectException(ValidationException::class);

        $this->getResource($request)
             ->addFields(
                 (new EditableField('name'))->rulesForUpdate('required')
             )
             ->update();

    }

    public function test_fields_are_validate_even_if_they_are_not_sent_on_the_request(): void
    {

        $request = $this->makeRequest('/', 'POST', [], StoreResourceRequest::class);

        $this->expectException(ValidationException::class);

        $this->getResource($request)
             ->addFields(
                 (new EditableField('name'))->rulesForCreate('required')
             )
             ->store();

    }

    public function test_rules_for_update_works_when_value_is_not_sent(): void
    {

        $request = $this->makeRequest('/', 'POST', [ 'name' => 'Test' ], UpdateResourceRequest::class);

        $this->expectException(ValidationException::class);

        $this->getResource($request)
             ->addFields(
                 (new EditableField('name'))->rulesForUpdate('required'),
                 (new EditableField('email'))->rulesForUpdate('required')
             )
             ->update();

    }

    public function test_field_can_resolve_default_value(): void
    {

        $request = $this->createRequest(UserResource::uriKey());

        $response = $this->makeResource($request, UserModel::class)
                         ->addFields(
                             EditableField::make('Name')->default('Demo'),
                             EditableField::make('Email')->default(fn() => 'demo@email.com'),
                         )
                         ->create();

        $this->assertEquals([
            'name' => 'Demo',
            'email' => 'demo@email.com',
        ], Arr::pluck($response->toArray(), 'value', 'attribute'));

    }

    public function getResource(BaseRequest $request): AbstractResource
    {
        return new class($request) extends AbstractResource {
            public static string $model = UserModel::class;
        };
    }

}
