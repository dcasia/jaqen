<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Http\Controllers;

use DigitalCreative\Dashboard\Concerns\WithFieldEvent;
use DigitalCreative\Dashboard\Fields\AbstractField;
use DigitalCreative\Dashboard\Http\Requests\DeleteResourceRequest;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use RuntimeException;

class DeleteController extends Controller
{

    public function delete(DeleteResourceRequest $request): Response
    {

        $ids = $request->input('ids');
        $resource = $request->resourceInstance();
        $repository = $resource->repository();

        $items = $repository->findByKeys($ids);
        $status = collect();

        foreach ($items as $model) {

            $fields = $resource->resolveFields($request)
                               ->whereInstanceOf(WithFieldEvent::class)
                               ->map(fn(AbstractField $field) => $field->hydrateFromModel($model, $request));

            $fields->each(fn(WithFieldEvent $field) => $field->runBeforeDelete($model));

            $status->push($repository->delete($model));

            $fields->each(fn(WithFieldEvent $field) => $field->runAfterDelete($model));

        }

        if ($status->filter()->count() !== $items->count()) {

            throw new RuntimeException('Failed to delete resources.');

        }

        return response()->noContent();

    }

}
