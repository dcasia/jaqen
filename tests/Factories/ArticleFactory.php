<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Factories;

use DigitalCreative\Jaqen\Tests\Fixtures\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @method Article|Collection<int, Article> create($attributes = [], ?Model $parent = null)
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
