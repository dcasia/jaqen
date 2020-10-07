<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Dashboard;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\FieldsResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\IndexResourceRequest;
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
    public function list(BaseRequest $request): Collection
    {
        return Dashboard::getInstance()
                        ->allAuthorizedResources($request)
                        ->map(function(AbstractResource $resource) {
                            return $resource->getDescriptor();
                        });
    }

    public function filters(IndexResourceRequest $request): array
    {
        return $request->resourceInstance()->getFiltersListing();
    }

    public function fields(FieldsResourceRequest $request): Collection
    {
        return $request->resourceInstance()->resolveFields($request);
    }

}
