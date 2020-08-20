<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use ArchyBold\LaravelMusicServices\Album;
use Faker\Generator as Faker;

$factory->define(Album::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'release_date' => $faker->date,
        'release_date_str' => $faker->date,
        'release_date_precision' => 'day',
        'url' => $faker->url,
        'vendor' => 'spotify',
        'vendor_id' => $faker->uuid,
    ];
});
