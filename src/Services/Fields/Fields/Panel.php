<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Fields\Fields;

use Closure;
use DigitalCreative\Jaqen\Concerns\BehaveAsPanel;

class Panel extends Proxy implements BehaveAsPanel
{

    private Closure|array $fields;

    public function __construct(string $label, array|callable $fields = [])
    {
        parent::__construct($label);

        $this->fields = $fields;
    }

    public function getFields(): FieldsCollection
    {
        return once(fn() => FieldsCollection::wrap(value($this->fields))->authorized());
    }

}
