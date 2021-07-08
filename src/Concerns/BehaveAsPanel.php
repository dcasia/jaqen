<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Concerns;

use DigitalCreative\Jaqen\Services\Fields\Fields\FieldsCollection;

interface BehaveAsPanel
{

    public function getFields(): FieldsCollection;

}
