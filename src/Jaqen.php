<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen;

class Jaqen
{
    private JaqenServiceProvider $provider;

    public function __construct(JaqenServiceProvider $provider)
    {
        $this->provider = $provider;
    }

    public function invokeProviderMethod(string $method)
    {
        if (method_exists($this->provider, $method)) {
            return $this->provider->$method();
        }

        return null;
    }

    public static function getInstance(): Jaqen
    {
        return app(__CLASS__);
    }
}
