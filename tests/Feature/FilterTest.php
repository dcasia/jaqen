<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature;

use DigitalCreative\Jaqen\Exceptions\FilterValidationException;
use DigitalCreative\Jaqen\Services\Fields\Fields\EditableField;
use DigitalCreative\Jaqen\Services\Fields\FieldsData;
use DigitalCreative\Jaqen\Services\ResourceManager\FilterCollection;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Filters\SampleFilter;
use DigitalCreative\Jaqen\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;

class FilterTest extends TestCase
{
    public function test_filter_works(): void
    {
        $user = UserFactory::new()->create([ 'name' => 'Demo' ]);

        UserFactory::new()->count(10)->create();

        $filter = new class extends SampleFilter
        {
            public function apply(Builder $builder, FieldsData $fieldsData): Builder
            {
                return $builder->where('name', 'Demo');
            }
        };

        $resource = $this->makeResource()
            ->addDefaultFields(new EditableField('name'))
            ->addFilters($filter);

        $this->resourceIndexApi($resource, filters: FilterCollection::fake([ $filter::uriKey() => null ]))
            ->assertJsonPath('total', 1)
            ->assertJsonPath('resources.0.key', $user->id)
            ->assertJsonPath('resources.0.fields.0.value', $user->name);
    }

    public function test_filter_validation_works(): void
    {
        $filter = new class extends SampleFilter
        {
            public function apply(Builder $builder, FieldsData $fieldsData): Builder
            {
                return $builder;
            }

            public function fields(): array
            {
                return [
                    EditableField::make('Name')->rules([ 'required', 'min:3' ]),
                ];
            }
        };

        $resource = $this->makeResource()->addFilters($filter);

        $this->withoutExceptionHandling();
        $this->expectException(FilterValidationException::class);

        $this->resourceIndexApi(
            $resource, filters: FilterCollection::fake([ $filter::uriKey() => null ]),
        );
    }

    public function test_multiple_filter_validation_works(): void
    {
        $filter1 = new class extends SampleFilter
        {
            public function fields(): array
            {
                return [
                    EditableField::make('Name')->rules('required'),
                ];
            }
        };

        $filter2 = new class extends SampleFilter
        {
            public function fields(): array
            {
                return [
                    EditableField::make('Gender')->rules('required'),
                ];
            }
        };

        $filters = FilterCollection::fake([
            $filter1::uriKey() => [ 'name' => 'Demo' ],
            $filter2::uriKey() => [ 'gender' => null ],
        ]);

        $resource = $this->makeResource()->addFilters($filter1, $filter2);

        $this->withoutExceptionHandling();
        $this->expectException(FilterValidationException::class);

        $this->resourceIndexApi($resource, filters: $filters);
    }

    public function test_value_from_the_fields_are_passed_correctly_to_the_apply_method(): void
    {
        $filter = new class($this) extends SampleFilter
        {
            private FilterTest $runner;

            public function __construct(FilterTest $runner)
            {
                $this->runner = $runner;
            }

            public function apply(Builder $builder, FieldsData $fieldsData): Builder
            {
                $this->runner->assertSame([ 'hello', 'world' ], $fieldsData->get('array'));
                $this->runner->assertSame('hello world', $fieldsData->get('string'));
                $this->runner->assertSame(2020, $fieldsData->get('int'));
                $this->runner->assertSame([ 'hello' => 'world' ], $fieldsData->get('object'));

                return $builder;
            }

            public function fields(): array
            {
                return [
                    EditableField::make('Array'),
                    EditableField::make('String'),
                    EditableField::make('Int'),
                    EditableField::make('Object'),
                ];
            }
        };

        $filters = FilterCollection::fake([
            $filter::uriKey() => [
                'array' => [ 'hello', 'world' ],
                'string' => 'hello world',
                'int' => 2020,
                'object' => [ 'hello' => 'world' ],
            ],
        ]);

        $resource = $this->makeResource()->addFilters($filter);

        $this->resourceIndexApi($resource, filters: $filters)
            ->assertOk();
    }
}
