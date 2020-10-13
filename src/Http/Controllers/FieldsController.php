<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Http\Requests\FieldsResourceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller;

class FieldsController extends Controller
{
    /**
     * Return a list of all available fields for a given resource
     *
     * @param FieldsResourceRequest $request
     *
     * @return JsonResponse
     */
    public function fields(FieldsResourceRequest $request): JsonResponse
    {
        return response()->json(
            $request->resourceInstance()->resolveFields($request)
        );
    }
}
