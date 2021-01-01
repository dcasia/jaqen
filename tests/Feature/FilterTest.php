<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature;

use DigitalCreative\Jaqen\Exceptions\FilterValidationException;
use DigitalCreative\Jaqen\Fields\EditableField;
use DigitalCreative\Jaqen\FieldsData;
use DigitalCreative\Jaqen\FilterCollection;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Filters\SampleFilter;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Jaqen\Tests\TestCase;
use DigitalCreative\Jaqen\Tests\Traits\RequestTrait;
use DigitalCreative\Jaqen\Tests\Traits\ResourceTrait;
use Illuminate\Database\Eloquent\Builder;

class FilterTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;

    public function test_filter_works(): void
    {

        $user = UserFactory::new()->create([ 'name' => 'Demo' ]);

        UserFactory::new()->count(10)->create();

        $filter = new class extends SampleFilter {

            public function apply(Builder $builder, FieldsData $value): Builder
            {
                return $builder->where('name', 'Demo');
            }

        };

        $resource = $this->makeResource(UserModel::class)
                         ->addDefaultFields(new EditableField('name'))
                         ->addFilters($filter);

        $response = $this->indexResponse($resource, [], [ 'filters' => FilterCollection::test([ $filter::uriKey() => null ]) ]);

        $this->assertSame(data_get($response, 'total'), 1);
        $this->assertEquals($user->id, data_get($response, 'resources.0.key'));
        $this->assertEquals($user->name, data_get($response, 'resources.0.fields.0.value'));

    }

    public function test_filter_validation_works(): void
    {

        $filter = new class extends SampleFilter {

            public function apply(Builder $builder, FieldsData $value): Builder
            {
                return $builder;
            }

            public function fields(): array
            {
                return [
                    (new EditableField('Name'))->rules('required', 'min:3'),
                ];
            }

        };

        $resource = $this->makeResource()->addFilters($filter);

        $this->expectException(FilterValidationException::class);

        $this->indexResponse(
            $resource, [], [ 'filters' => FilterCollection::test([ $filter::uriKey() => null ]) ]
        );

    }

    public function test_multiple_filter_validation_works(): void
    {

        $filter1 = new class extends SampleFilter {

            public function fields(): array
            {
                return [
                    (new EditableField('Name'))->rules('required'),
                ];
            }

        };

        $filter2 = new class extends SampleFilter {

            public function fields(): array
            {
                return [
                    (new EditableField('Gender'))->rules('required'),
                ];
            }

        };

        $filters = FilterCollection::test([
            $filter1::uriKey() => [ 'name' => 'Demo' ],
            $filter2::uriKey() => [ 'gender' => null ],
        ]);

        $resource = $this->makeResource(UserModel::class)->addFilters($filter1, $filter2);

        $this->expectException(FilterValidationException::class);

        $this->indexResponse($resource, [], [ 'filters' => $filters ]);

    }

    public function test_value_from_the_fields_are_passed_correctly_to_the_apply_method(): void
    {

        $filter = new class($this) extends SampleFilter {

            private FilterTest $runner;

            public function __construct(FilterTest $runner)
            {
                $this->runner = $runner;
            }

            public function apply(Builder $builder, FieldsData $value): Builder
            {
                $this->runner->assertSame([ 'hello', 'world' ], $value->get('array'));
                $this->runner->assertSame('hello world', $value->get('string'));
                $this->runner->assertSame(2020, $value->get('int'));
                $this->runner->assertSame([ 'hello' => 'world' ], $value->get('object'));

                return $builder;
            }

            public function fields(): array
            {
                return [
                    new EditableField('Array'),
                    new EditableField('String'),
                    new EditableField('Int'),
                    new EditableField('Object'),
                ];
            }

        };

        $filters = FilterCollection::test([
            $filter::uriKey() => [
                'array' => [ 'hello', 'world' ],
                'string' => 'hello world',
                'int' => 2020,
                'object' => [ 'hello' => 'world' ],
            ],
        ]);

        $resource = $this->makeResource()->addFilters($filter);

        $this->indexResponse($resource, [ 'filters' => $filters ]);

    }

}
