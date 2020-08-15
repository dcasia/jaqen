<?php

declare(strict_types = 1);

use DigitalCreative\Dashboard\Tests\Fixtures\Models\Article;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

/**
 * @var EloquentFactory $factory
 */
$factory->define(Article::class, static function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'content' => $faker->text,
        'user_id' => static function () {
            return factory(User::class)->create()->getKey();
        },
    ];
});
