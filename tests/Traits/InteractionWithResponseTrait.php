<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Traits;

use JsonException;

trait InteractionWithResponseTrait
{
    /**
     * @param array|object $object
     *
     * @return array
     */
    protected function deepSerialize($object): array
    {
        try {

            return json_decode(json_encode($object, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        } catch (JsonException $exception) {

            $this->fail($exception->getMessage());

        }
    }

}
