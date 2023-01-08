<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Factories;

use DigitalCreative\Jaqen\Tests\Fixtures\Models\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @method Phone|Collection<int, Phone> create($attributes = [], ?Model $parent = null)
 */
class PhoneFactory extends Factory
{
    protected $model = Phone::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->randomNumber(9),
            'user_id' => UserFactory::new(),
        ];
    }
}
