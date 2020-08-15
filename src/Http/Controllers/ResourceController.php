<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\CreateResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\DetailResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\IndexResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use Illuminate\Routing\Controller;

class ResourceController extends Controller
{

    public function searchBelongsTo(BaseRequest $request)
    {
        return $request->resourceInstance()->searchBelongsToRelation();
    }

    public function filters(IndexResourceRequest $request)
    {
        return $request->resourceInstance()->getFiltersListing();
    }

    public function index(IndexResourceRequest $request)
    {
        return $request->resourceInstance()->index();
    }

    public function update(UpdateResourceRequest $request)
    {
        return $request->resourceInstance()->update();
    }

    public function create(CreateResourceRequest $request)
    {
        return $request->resourceInstance()->create();
    }

    public function fetch(DetailResourceRequest $request)
    {
        return $request->resourceInstance()->detail();
    }

}
