<?php

namespace DigitalCreative\Dashboard\Fields;

class PasswordField extends AbstractField
{

    public function __construct(string $label, string $attribute = null)
    {
        parent::__construct($label, $attribute);

        $this->rulesForUpdate('sometimes', 'required', 'min:8');
        $this->rulesForCreate('required', 'min:8');
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [ 'value' => null ]);
    }

}
