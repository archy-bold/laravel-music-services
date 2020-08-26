<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use ArchyBold\LaravelMusicServices\Album;
use Faker\Generator as Faker;

$factory->define(Album::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'artists' => $faker->company,
        'upc' => $faker->randomNumber(8, true),
        'release_date' => $faker->date,
        'url' => $faker->url,
        'vendor' => 'spotify',
        'vendor_id' => $faker->uuid,
    ];
});
