<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Http\Requests\DeleteResourceRequest;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use RuntimeException;

class DeleteController extends Controller
{

    public function delete(DeleteResourceRequest $request): Response
    {

        $ids = $request->input('ids');

        if ($request->resourceInstance()->repository()->batchDelete($ids)) {

            return response()->noContent();

        }

        throw new RuntimeException('Failed to delete resources.');

    }

}
