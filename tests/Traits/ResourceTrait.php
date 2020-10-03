<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Traits;

use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use Illuminate\Database\Eloquent\Model;

trait ResourceTrait
{

    protected function makeResource(BaseRequest $request, string $model = UserModel::class): AbstractResource
    {
        return new class($request, $model) extends AbstractResource {

            public string $model;

            public function __construct(BaseRequest $request, string $model)
            {
                parent::__construct($request);

                $this->model = $model;
            }

            public function getModel(): Model
            {
                return new $this->model;
            }

        };
    }

}

