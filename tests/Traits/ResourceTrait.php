<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Traits;

use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Services\ResourceManager\ResourceManager;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use Illuminate\Support\Facades\Gate;

trait ResourceTrait
{

    protected function makeResource(string $model = UserModel::class): AbstractResource
    {
        $resource = new class extends AbstractResource { };

        $resource::$model = $model;

        $this->registerResource($resource);

        return $resource;

    }

    protected function registerResource(AbstractResource|string ...$resources): static
    {
        /**
         * @var ResourceManager $resourceManager
         */
        $resourceManager = resolve(ResourceManager::class);
        $resourceManager->setResources($resources);

        return $this;
    }

    protected function registerPolicy(AbstractResource|string $resource, string $policy)
    {
        Gate::policy($resource::$model, $policy);
    }

}

