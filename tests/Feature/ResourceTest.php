<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature;

use DigitalCreative\Dashboard\Fields\EditableField;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;

class ResourceTest extends TestCase
{

    use ResourceTrait;
    use RequestTrait;

    public function test_invokable_fields_works(): void
    {

        $invokable = new class {

            public function __invoke()
            {
                return [
                    new EditableField('Test'),
                ];
            }

        };

        $fields = $this->makeResource()
                       ->fieldsFor('demo', fn() => [ $invokable ])
                       ->resolveFields($this->blankRequest([], [ 'fieldsFor' => 'demo' ]));

        $this->assertEquals($fields->first(), $invokable);

    }

}
