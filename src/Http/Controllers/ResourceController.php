<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Dashboard;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
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
        return Dashboard::getInstance()
                        ->allAuthorizedResources($request)
                        ->map(fn(AbstractResource $resource) => $resource->getDescriptor());
    }

}
