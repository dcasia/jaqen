<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Scaffold;

use DigitalCreative\Jaqen\Resources\AbstractResource;
use DigitalCreative\Jaqen\Traits\AuthorizableTrait;
use DigitalCreative\Jaqen\Traits\MakeableTrait;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;

abstract class Link implements SidebarInterface
{
    use MakeableTrait;
    use AuthorizableTrait;

    private string $title;
    private ?string $icon = null;
    private bool $fixed = false;
    private array $entries = [];

    public function icon(string $icon): self
    {
        $this->icon = $icon;

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
