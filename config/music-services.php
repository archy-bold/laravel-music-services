<?php

return [
    'override_migrations' => false,

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
