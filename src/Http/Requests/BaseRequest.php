<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Http\Requests;

use DigitalCreative\Jaqen\Jaqen;
use DigitalCreative\Jaqen\Resources\AbstractResource;
use DigitalCreative\Jaqen\Services\Crud\Http\Requests\StoreResourceRequest;
use DigitalCreative\Jaqen\Services\Crud\Http\Requests\UpdateResourceRequest;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    public function resourceInstance(): AbstractResource
    {
        return Jaqen::getInstance()->resourceForRequest($this);
    }

    public function isCreate(): bool
    {
        return $this instanceof StoreResourceRequest
            || $this instanceof FieldsResourceRequest;
    }

    public function isSchemaFetching(): bool
    {
        return $this instanceof FieldsResourceRequest;
    }

    public function isStoringResourceToDatabase(): bool
    {
        return $this instanceof StoreResourceRequest
            || $this instanceof UpdateResourceRequest;
    }

}
