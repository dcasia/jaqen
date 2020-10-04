<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\FilterCollection;
use DigitalCreative\Dashboard\Http\Requests\IndexResourceRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;

class IndexController extends Controller
{

    public function index(IndexResourceRequest $request): array
    {

        $resource = $request->resourceInstance();

        $fields = $resource->resolveFields($request);

        $filters = new FilterCollection($resource->resolveFilters(), $request->query('filters'));

        $total = $resource->repository()->count($filters);

        $resources = $resource->repository()
                              ->findCollection($filters, (int) $request->query('page', 1))
                              ->map(function(Model $model) use ($request, $fields) {

                                  return [
                                      'key' => $model->getKey(),
                                      'fields' => $fields->map(function(AbstractField $field) use ($model, $request) {
                                          return (clone $field)->resolveValueFromModel($model, $request);
                                      }),
                                  ];

                              });

        return [
            'total' => $total,
            'resources' => $resources,
        ];
    }

}
