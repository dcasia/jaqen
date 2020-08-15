<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard;

use DigitalCreative\Dashboard\Http\Requests\BaseRequest;

class Dashboard
{

    private array $resources;

    public function setResources(array $resources): self
    {
        $this->resources = $resources;

        return $this;
    }

    public static function getInstance(): Dashboard
    {
        return app(__CLASS__);
    }

    public function resourceForRequest(BaseRequest $request): AbstractResource
    {
        return once(function () use ($request) {

            /**
             * @todo Create a cache system to dont have to loop through every single resource every time
             *
             * @var AbstractResource $resource
             */
            foreach ($this->resources as $resource) {

                if ($resource::uriKey() === $request->route('resource')) {

                    return new $resource($request);

                }

            }

        });
    }

}
