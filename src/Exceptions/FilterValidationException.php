<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Exceptions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class FilterValidationException extends ValidationException
{
    public static function fromValidationExceptions(array $exceptions): self
    {
        $payload = [];

        /**
         * @var ValidationException $exception
         */
        foreach ($exceptions as $filterUriKey => $exception) {
            $payload[ $filterUriKey ] = [ $exception->validator->errors()->messages() ];
        }

        return self::withMessages($payload);
    }

    public function errors(): array
    {
        $errors = [];

        foreach ($this->validator->errors()->messages() as $filterUriKey => $error) {
            $errors[ $filterUriKey ] = Arr::collapse($error);
        }

        return $errors;
    }
}
