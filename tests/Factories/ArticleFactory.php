<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Factories;

use App\Models\User;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ArticleFactory
 *
 * @method Article|Collection create($attributes = [], ?Model $parent = null)
 *
 * @package DigitalCreative\Dashboard\Tests\Factories
 */
class ArticleFactory extends Factory
{

    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->text,
            'user_id' => UserFactory::new(),
        ];
    }
}
