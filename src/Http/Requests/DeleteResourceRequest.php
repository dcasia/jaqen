<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Http\Requests;

class DeleteResourceRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'ids' => 'required|array',
        ];
    }
}
