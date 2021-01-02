<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Scaffold;

use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Traits\AuthorizableTrait;
use DigitalCreative\Jaqen\Traits\MakeableTrait;

class ExternalLink extends ResourceLink
{
    use MakeableTrait;
    use AuthorizableTrait;

    private string $title;
    private AbstractResource $resource;
    private ?string $icon = null;
    private bool $fixed = false;
    private array $entries = [];

    public function __construct(string $title, string $resource)
    {
        $this->title = $title;
        $this->resource = $resource;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function fixed(bool $fixed = true): self
    {
        $this->fixed = $fixed;

        return $this;
    }

    public function addSubmenu(string $header, array $entries): self
    {
        $this->entries[] = [
            'header' => $header, 'entries' => $entries,
        ];

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title,
            'icon' => $this->icon,
            'fixed' => $this->fixed,
            'resource' => $this->resource->getDescriptor(),
            'entries' => $this->entries->jsonSerialize(),
        ];
    }
}
