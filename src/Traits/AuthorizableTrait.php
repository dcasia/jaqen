<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Traits;

use Illuminate\Support\Facades\Gate;

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

    public function authorizeToView(): bool
    {
        if ($authorization = $this->authorization()) {

            if (method_exists($authorization, 'view')) {

                return Gate::check('view', $this->newModel());

            }

            return false;

        }

        return true;
    }

    public function authorization(): ?object
    {
        return Gate::getPolicyFor($this::$model);
    }

}
