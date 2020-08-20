<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use ArchyBold\LaravelMusicServices\Playlist;
use Faker\Generator as Faker;

$factory->define(Playlist::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'vendor' => 'spotify',
        'vendor_id' => $faker->uuid,
        'url' => $faker->url,
        'owner_id' => factory(ArchyBold\LaravelMusicServices\User::class)->create()->id,
    ];
});
