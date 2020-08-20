<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use ArchyBold\LaravelMusicServices\Track;
use Faker\Generator as Faker;

$factory->define(Track::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'artists' => $faker->company,
        'vendor' => 'spotify',
        'vendor_id' => $faker->uuid,
        'url' => $faker->url,
        'album_id' => factory(\ArchyBold\LaravelMusicServices\Album::class)->create()->id,
    ];
});
