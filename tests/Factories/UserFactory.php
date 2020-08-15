<?php

declare(strict_types = 1);

use DigitalCreative\Dashboard\Tests\Fixtures\Models\Article;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

/**
 * @var EloquentFactory $factory
 */
$factory->define(User::class, static function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'gender' => $faker->randomElement([ 'male', 'female' ]),
        'password' => $faker->password
    ];
});

$factory->afterCreatingState(User::class, 'with-articles', static function (User $user, Faker $faker) {
    factory(Article::class, 5)->create([ 'user_id' => $user->id ]);
});

$factory->afterCreatingState(User::class, 'with-article', static function (User $user, Faker $faker) {
    factory(Article::class)->create([ 'user_id' => $user->id ]);
});
