<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Factories;

use App\Models\User;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class PhoneFactory
 *
 * @method Phone|Collection create($attributes = [], ?Model $parent = null)
 *
 * @package DigitalCreative\Dashboard\Tests\Factories
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
