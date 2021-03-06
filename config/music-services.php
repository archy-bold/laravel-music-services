<?php

return [
    'override_migrations' => false,

    'models' => [
        'album' => ArchyBold\LaravelMusicServices\Album::class,
        'playlist' => ArchyBold\LaravelMusicServices\Playlist::class,
        'playlist_snapshot' => ArchyBold\LaravelMusicServices\PlaylistSnapshot::class,
        'track' => ArchyBold\LaravelMusicServices\Track::class,
        'track_information' => ArchyBold\LaravelMusicServices\TrackInformation::class,
        'user' => ArchyBold\LaravelMusicServices\User::class,
    ],

    'table_names' => [
        'albums' => 'lms_albums',
        'playlists' => 'lms_playlists',
        'playlist_snapshots' => 'lms_playlist_snapshots',
        'tracks' => 'lms_tracks',
        'track_information' => 'lms_track_information',
        'users' => 'lms_users',
        'playlist_snapshot_track_pivot' => 'lms_playlist_snapshot_track',
    ],

    'spotify' => [
        'client_id' => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    ],
];
