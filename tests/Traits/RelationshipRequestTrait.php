<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Traits;

use DigitalCreative\Dashboard\Fields\BelongsToField;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\BelongsToResourceRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use Illuminate\Database\Eloquent\Model;

trait RelationshipRequestTrait
{

    protected function belongsToSearchRequest(AbstractResource $resource, BelongsToField $field, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/belongs-to/{resource}/{field}' => sprintf("/belongs-to/%s/%s", $resource::uriKey(), $field->getRelationAttribute()) ],
            'GET',
            $data,
            $query,
            BelongsToResourceRequest::class
        );
    }

}
