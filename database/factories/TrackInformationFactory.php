<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use ArchyBold\LaravelMusicServices\TrackInformation;
use Faker\Generator as Faker;

$factory->define(TrackInformation::class, function (Faker $faker) {
    return [
        'type' => TrackInformation::AUDIO_FEATURES,
        'vendor' => 'spotify',
        'meta' => [],
    ];
});
