<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests;

use DigitalCreative\Jaqen\Http\Requests\BaseRequest;

class DeleteResourceRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'ids' => [ 'required', 'array' ],
        ];
    }
}
