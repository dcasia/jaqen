<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Fields\Relationships;

use DigitalCreative\Jaqen\Fields\AbstractField;

abstract class Relationship extends AbstractField
{

    protected ?string $relatedResource = null;
    protected ?string $relatedFieldsFor = null;

    public function setRelatedResource(string $relatedResource, string $fieldsFor = null): self
    {
        $this->relatedResource = $relatedResource;

        if ($fieldsFor) {
            $this->setRelatedResourceFieldsFor($fieldsFor);
        }

        return $this;
    }

    public function setRelatedResourceFieldsFor(string $fieldsFor): self
    {
        $this->relatedFieldsFor = $fieldsFor;

        return $this;
    }

}
