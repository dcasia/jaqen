<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Http\Requests\DeleteResourceRequest;
use Illuminate\Routing\Controller;

class DeleteController extends Controller
{

    public function delete(DeleteResourceRequest $request): bool
    {
        $resource = $request->resourceInstance();

        $model = $resource->repository()->findByKey($request->route('key'));

        return $resource->repository()->deleteResource($model);
    }

}
