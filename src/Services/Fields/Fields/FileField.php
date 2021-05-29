<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Fields\Fields;

use DigitalCreative\Jaqen\Concerns\WithEvents;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Traits\EventsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileField extends AbstractField implements WithEvents
{

    use EventsTrait;

    private string $disk = 'public';
    private bool $pruneFile = false;

    public function resolveValueFromArray(array $data, BaseRequest $request): self
    {
        /**
         * @var UploadedFile|string|null $file
         */
        $file = data_get($data, $this->attribute);

        if ($file instanceof UploadedFile) {

            $path = $file->store('images', [ 'disk' => $this->disk ]);

            $this->setValue($path, $request);

        } else if (is_string($file)) {

            /**
             * If a string is sent assume user wants to keep the same file
             */

        } else if (is_null($file)) {

            /**
             * If null, assume user wants to delete the existing attached resource
             */
            $this->unlinkAsset($this->value);
            $this->setValue(null, $request);

        }

        return $this;
    }

    public function disk(string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    public function pruneFile(bool $prune = true): self
    {
        $this->pruneFile = $prune;

        $this->beforeDelete(function (Model $model) {
            $this->unlinkAsset($model->getAttribute($this->attribute));
        });

        return $this;
    }

    private function unlinkAsset(string $asset): bool
    {
        if ($this->pruneFile) {
            return Storage::disk($this->disk)->delete($asset);
        }

        return false;
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'value' => Storage::disk($this->disk)->url($this->value),
        ]);
    }

}
