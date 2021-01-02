<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers;

use DigitalCreative\Jaqen\Concerns\WithEvents;
use DigitalCreative\Jaqen\Fields\AbstractField;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\DeleteResourceRequest;
use Illuminate\Http\Response;
use RuntimeException;

class DeleteController extends Controller
{

    public function handle(DeleteResourceRequest $request): Response
    {

        $ids = $request->input('ids');
        $resource = $this->resourceManager->resourceForRequest($request);
        $repository = $resource->repository();

        $items = $repository->findByKeys($ids);
        $status = collect();

        foreach ($items as $model) {

            $fields = $resource->resolveFields($request)
                               ->whereInstanceOf(WithEvents::class)
                               ->map(fn(AbstractField $field) => $field->hydrateFromModel($model, $request));

            $fields->each(fn(WithEvents $field) => $field->runBeforeDelete($model));
            $resource->runBeforeDelete($model);

            $status->push($repository->delete($model));

            $fields->each(fn(WithEvents $field) => $field->runAfterDelete($model));
            $resource->runAfterDelete($model);

        }

        if ($status->filter()->count() !== $items->count()) {

            throw new RuntimeException('Failed to delete resources.');

        }

        return response()->noContent();

    }

}
