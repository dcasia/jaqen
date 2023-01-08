<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Fields\Fields;

use Illuminate\Validation\Rules\Password;

class PasswordField extends AbstractField
{
    public function __construct(string $label, string $attribute = null)
    {
        parent::__construct($label, $attribute);

        $this->rulesForUpdate([ 'sometimes', 'required', Password::default() ])
            ->rulesForCreate([ 'required', Password::default() ]);
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [ 'value' => null ]);
    }
}
