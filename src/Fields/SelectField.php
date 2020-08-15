<?php

namespace DigitalCreative\Dashboard\Fields;

class SelectField extends AbstractField
{
    public function options(array $options): self
    {
        return $this->withAdditionalInformation($options);
    }
}
