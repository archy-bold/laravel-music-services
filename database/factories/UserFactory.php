<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use ArchyBold\LaravelMusicServices\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'vendor' => 'spotify',
        'vendor_id' => $faker->uuid,
        'url' => $faker->url,

    ];
});
