<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Traits;

use DigitalCreative\Jaqen\Jaqen;
use DigitalCreative\Jaqen\Resources\AbstractResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use Illuminate\Database\Eloquent\Model;

trait ResourceTrait
{

    protected function makeResource(string $model = UserModel::class): AbstractResource
    {
        $resource = new class($model) extends AbstractResource {

            public string $model;

            public function __construct(string $model)
            {
                $this->model = $model;
            }

            public function model(): Model
            {
                return new $this->model;
            }

        };

        $this->registerResource($resource);

        return $resource;

    }

    private function registerResource(AbstractResource $resource): void
    {
        Jaqen::getInstance()->setResources([ $resource ]);
    }

}

