<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers;

use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use Illuminate\Support\Collection;

class ResourceController extends Controller
{

    /**
     * Return a list of all registered resources
     */
    public function resources(): Collection
    {
        return $this->resourceManager
            ->allAuthorizedResources()
            ->map(fn(AbstractResource $resource) => $resource->getDescriptor());
    }

}
