<?php

use DigitalCreative\Dashboard\Tests\Fixtures\Models\Client;
use Faker\Generator as Faker;

$factory->define(Client::class, static function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'gender' => $faker->randomElement([ 'male', 'female' ]),
        'password' => $faker->password
    ];
});
