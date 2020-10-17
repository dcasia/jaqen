<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Factories;

use DigitalCreative\Dashboard\Tests\Fixtures\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class RoleFactory
 *
 * @method Role|Collection create($attributes = [], ?Model $parent = null)
 *
 * @package DigitalCreative\Dashboard\Tests\Factories
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
