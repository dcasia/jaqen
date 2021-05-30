<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Throwable;

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

    /**
     * @throws AuthorizationException|Throwable
     */
    public function authorizeTo(string $ability): void
    {
        throw_unless($this->authorizedTo($ability), AuthorizationException::class);
    }

    public function authorizedTo(string $ability): bool
    {
        if ($authorization = $this->authorization()) {

            if (method_exists($authorization, $ability)) {

                return Gate::check($ability, $this->newModel());

            }

            return false;

        }

        return true;
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
