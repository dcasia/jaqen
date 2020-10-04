<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Traits;

use DigitalCreative\Dashboard\Dashboard;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use Illuminate\Database\Eloquent\Model;

trait ResourceTrait
{

    protected function makeResource(string $model = UserModel::class): AbstractResource
    {
        $resource = new class($model) extends AbstractResource {

            public string $model;

            public function __construct(string $model)
            {
                parent::__construct();
                $this->model = $model;
            }

            public function getModel(): Model
            {
                return new $this->model;
            }

        };

        $this->registerResource($resource);

        return $resource;

    }

    private function registerResource(AbstractResource $resource): void
    {
        Dashboard::getInstance()->setResources([ $resource ]);
    }

}

