<?php

namespace DigitalCreative\Dashboard\Fields;

class PasswordField extends AbstractField
{

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [ 'value' => null ]);
    }

}
