<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Scaffold;

use DigitalCreative\Jaqen\Traits\AuthorizableTrait;
use DigitalCreative\Jaqen\Traits\MakeableTrait;

class ResourceLink implements SidebarInterface
{
    use MakeableTrait;
    use AuthorizableTrait;

    private string $title;
    private string $resource;
    private ?string $icon = null;
    private bool $fixed = false;
    private bool $topLevel = false;
    private array $entries = [];

    public function __construct(string $title, string $resource)
    {
        $this->title = $title;
        $this->resource = $resource;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function fixed(bool $fixed = true): static
    {
        $this->fixed = $fixed;

        return $this;
    }

    public function topLevel(): static
    {
        $this->topLevel = true;

        return $this;
    }

    public function addSubmenu(string $header, array $entries): static
    {
        $this->entries[] = [
            'label' => $header, 'items' => $entries,
        ];

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'label' => $this->title,
            'icon' => $this->icon,
            'fixed' => $this->fixed,
            'route' => $this->topLevel ? null : [
                'name' => 'ResourceIndex',
                'params' => [
                    'uriKey' => $this->resource::uriKey(),
                ],
            ],
            'entries' => $this->entries,
        ];
    }
}
