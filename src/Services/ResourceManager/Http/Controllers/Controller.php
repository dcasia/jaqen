<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers;

use DigitalCreative\Jaqen\Services\ResourceManager\ResourceManager;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{

    protected ResourceManager $resourceManager;

    public function __construct()
    {
        $this->resourceManager = resolve(ResourceManager::class);
    }

}
