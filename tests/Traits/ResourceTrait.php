<?php

namespace DigitalCreative\Dashboard\Tests\Traits;

use DigitalCreative\Dashboard\AbstractResource;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Client;

trait ResourceTrait
{

    protected function getResource(BaseRequest $request, string $model = Client::class): AbstractResource
    {
        return new class($request, $model) extends AbstractResource {

            public static string $model;

            public function __construct(BaseRequest $request, string $model)
            {
                parent::__construct($request);

                static::$model = $model;
            }

        };
    }

}

