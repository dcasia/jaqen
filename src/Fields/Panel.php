<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Fields;

use DigitalCreative\Jaqen\Concerns\BehaveAsPanel;
use DigitalCreative\Jaqen\Http\Requests\BaseRequest;

class Panel extends AbstractField implements BehaveAsPanel
{

    /**
     * @var array|callable
     */
    private $fields;

    /**
     * Panel constructor.
     *
     * @param string $label
     * @param array|callable $fields
     */
    public function __construct(string $label, $fields = [])
    {
        parent::__construct($label);

        $this->fields = $fields;
    }

    public function resolveValueFromArray(array $data, BaseRequest $request): self
    {
        /**
         * @var AbstractField $field
         */
        foreach ($fields = $this->getFields() as $field) {

            $field->resolveValueFromArray($data, $request);

        }

        return $this->setValue($fields, $request);
    }

    public function getFields(): array
    {
        return value($this->fields);
    }

}
