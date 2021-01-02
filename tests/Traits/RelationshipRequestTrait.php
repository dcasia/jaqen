<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Traits;

use DigitalCreative\Jaqen\Fields\Relationships\BelongsToField;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Http\Requests\BelongsToResourceRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;

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
