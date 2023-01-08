<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Factories;

use DigitalCreative\Jaqen\Tests\Fixtures\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @method Role|Collection<int, Role> create($attributes = [], ?Model $parent = null)
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
