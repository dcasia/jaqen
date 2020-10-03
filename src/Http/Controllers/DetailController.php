<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Http\Requests\DetailResourceRequest;
use Illuminate\Routing\Controller;

class DetailController extends Controller
{

    public function detail(DetailResourceRequest $request): array
    {
        $resource = $request->resourceInstance();

        $model = $resource->repository()->findByKey($request->route('key'));

        return [
            'key' => $model->getKey(),
            'fields' => $resource->resolveFieldsUsingModel($model, $request)->jsonSerialize(),
        ];
    }

}
