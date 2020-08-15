<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Traits;

use DigitalCreative\Dashboard\AbstractResource;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;

trait ResourceTrait
{

    protected function makeResource(BaseRequest $request, string $model = UserModel::class): AbstractResource
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

