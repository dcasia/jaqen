<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Http\Controllers;

use DigitalCreative\Jaqen\Jaqen;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Resources\AbstractResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class ResourceController extends Controller
{

    /**
     * Return a list of all registered resources
     *
     * @param BaseRequest $request
     * @return Collection
     */
    public function resources(BaseRequest $request): Collection
    {
        return Jaqen::getInstance()
                    ->allAuthorizedResources($request)
                    ->map(fn(AbstractResource $resource) => $resource->getDescriptor());
    }

}
