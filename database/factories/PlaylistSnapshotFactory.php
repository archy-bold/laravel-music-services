<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use ArchyBold\LaravelMusicServices\PlaylistSnapshot;
use Faker\Generator as Faker;

$factory->define(PlaylistSnapshot::class, function (Faker $faker) {
    return [
        'num_followers' => $faker->numberBetween(0, 1000000),
        'playlist_id' => factory(ArchyBold\LaravelMusicServices\Playlist::class)->create()->id,
    ];
});
