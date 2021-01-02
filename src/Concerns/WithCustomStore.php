<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Concerns;

use DigitalCreative\Jaqen\Services\Crud\Http\Requests\StoreResourceRequest;

interface WithCustomStore
{
    /**
     * The return of this function is sent back to the client after the creation
     * Avoid returning sensitive information, like raw user passwords
     *
     * @param array $data
     * @param StoreResourceRequest $request
     * @return mixed
     */
    public function storeResource(array $data, StoreResourceRequest $request);
}
