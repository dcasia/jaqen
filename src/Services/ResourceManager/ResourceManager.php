<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager;

use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use Illuminate\Support\Collection;

class ResourceManager
{

    private Collection $resources;

    public function __construct()
    {
        $this->resources = collect();
    }

    public function setResources(array $resources): self
    {
        $this->resources = $this->resources
            ->merge($resources)
            ->mapWithKeys(fn(AbstractResource|string $resource) => [ $resource::uriKey() => $resource ]);

        return $this;
    }

    public function allAuthorizedResources(): Collection
    {
        return $this->resources
            ->map(fn(AbstractResource|string $resource) => $resource instanceof AbstractResource ? $resource : resolve($resource))
            ->filter(fn(AbstractResource $resource) => $resource->authorizeToView())
            ->values();
    }

    public function resourceForRequest(BaseRequest $request): AbstractResource
    {
        return once(function () use ($request) {

            if ($resource = $this->resources->get($request->route('resource'))) {

                if (!$resource instanceof AbstractResource) {

                    $resource = new $resource();

                }

                return $resource->bootFields($request);

            }

        });
    }

}
