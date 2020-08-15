<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature;

use DigitalCreative\Dashboard\AbstractResource;
use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\CreateResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use Illuminate\Validation\ValidationException;

class FieldTest extends TestCase
{

    use RequestTrait;

    public function test_field_validation_on_create_works(): void
    {

        $request = $this->makeRequest('/', 'POST', [ 'name' => null ], CreateResourceRequest::class);

        $this->expectException(ValidationException::class);

        $this->getResource($request)
             ->addFields(
                 (new EditableField('name'))->rulesForCreate('required')
             )
             ->create();

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

        $request = $this->makeRequest('/', 'POST', [], CreateResourceRequest::class);

        $this->expectException(ValidationException::class);

        $this->getResource($request)
             ->addFields(
                 (new EditableField('name'))->rulesForCreate('required')
             )
             ->create();

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

    public function getResource(BaseRequest $request): AbstractResource
    {
        return new class($request) extends AbstractResource {
            public static string $model = UserModel::class;
        };
    }

}
