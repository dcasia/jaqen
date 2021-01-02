<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Http\Controllers;

use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\Controller;
use Illuminate\Support\Collection;

class ResourceController extends Controller
{

    /**
     * Return a list of all registered resources
     *
     * @param BaseRequest $request
     *
     * @return Collection
     */
    public function resources(BaseRequest $request): Collection
    {
        return $this->resourceManager->allAuthorizedResources($request)
                                     ->map(fn(AbstractResource $resource) => $resource->getDescriptor());
    }

}
