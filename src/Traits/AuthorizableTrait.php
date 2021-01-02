<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Traits;

trait AuthorizableTrait
{
    /**
     * @var bool|callable
     */
    private $authorizationCallback;

    public function canSee(bool|callable $callback): static
    {
        $this->authorizationCallback = $callback;

        return self;
    }

    public function isAuthorizedToSee(): bool
    {
        return value($this->authorizationCallback) ?: true;
    }

}
