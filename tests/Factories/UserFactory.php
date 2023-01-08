<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Factories;

use DigitalCreative\Jaqen\Tests\Fixtures\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @method User|Collection<int, User> create($attributes = [], ?Model $parent = null)
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'gender' => $this->faker->randomElement([ 'male', 'female' ]),
            'password' => $this->faker->password,
        ];
    }

    public function withPhone(): self
    {
        return $this->afterCreating(function (User $user) {
            return PhoneFactory::new()->create([ 'user_id' => $user->id ]);
        });
    }
}
