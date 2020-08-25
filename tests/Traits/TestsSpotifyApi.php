<?php

namespace ArchyBold\LaravelMusicServices\Tests\Traits;

trait TestsSpotifyApi
{
    public function getExpectedPlaylist()
    {
        return $this->getExpectedUserPlaylists()[0];
    }

    public function getExpectedCreatedPlaylist()
    {
        return [
            'name' => 'New Playlist',
            'url' => 'https://open.spotify.com/playlist/02OUOzIuIE0h1zrQBFnc0n',
            'vendor' => 'spotify',
            'vendor_id' => '02OUOzIuIE0h1zrQBFnc0n',
            'public' => false,
            'description' => 'New playlist description',
            'owner_id' => null,
            'meta' => [
                'collaborative' => false,
                'images' => [],
            ],
        ];
    }

    public function getExpectedUser()
    {
        return [
            'name' => 'Simon Archer',
            'meta' => [],
            'url' => 'https://open.spotify.com/user/archy_bold',
            'vendor' => 'spotify',
            'vendor_id' => 'archy_bold',
        ];
    }

    public function getExpectedPlaylistSnapshot()
    {
        return [
            'num_followers' => 6,
            'meta' => [],
        ];
    }

    public function getExpectedPlaylistTracks()
    {
        return [
            [
                'title' => 'Your Love (feat. Jamie Principle)',
                'artists' => 'Frankie Knuckles, Jamie Principle',
                'album' => [
                    'name' => 'Four Most Cuts Presents - Frankie Knuckles vs. Mr Fingers',
                    'release_date' => '1987-06-01',
                    'release_date_str' => '1987',
                    'release_date_precision' => 'year',
                    'meta' => [
                        'available_markets' => ['AD', 'AE', 'AL', 'AR', 'AT', 'AU', 'BA', 'BE', 'BG', 'BH', 'BO', 'BR', 'BY', 'CA', 'CH', 'CL', 'CO', 'CR', 'CY', 'CZ', 'DE', 'DK', 'DO', 'DZ', 'EC', 'EE', 'EG', 'ES', 'FI', 'FR', 'GB', 'GR', 'GT', 'HK', 'HN', 'HR', 'HU', 'ID', 'IE', 'IL', 'IS', 'IT', 'JO', 'JP', 'KW', 'KZ', 'LB', 'LI', 'LT', 'LU', 'LV', 'MA', 'MC', 'MD', 'ME', 'MK', 'MT', 'MX', 'MY', 'NI', 'NL', 'NO', 'NZ', 'OM', 'PA', 'PE', 'PH', 'PL', 'PS', 'PT', 'PY', 'QA', 'RO', 'RS', 'RU', 'SA', 'SE', 'SG', 'SI', 'SK', 'SV', 'TH', 'TN', 'TR', 'TW', 'UA', 'US', 'UY', 'VN', 'XK', 'ZA'],
                        'images' => [
                            [
                                'height' => 640,
                                'width' => 640,
                                'url' => 'https://i.scdn.co/image/ab67616d0000b273e71c53b1e7e74ddcaa427755',
                            ],
                            [
                                'height' => 300,
                                'width' => 300,
                                'url' => 'https://i.scdn.co/image/ab67616d00001e02e71c53b1e7e74ddcaa427755',
                            ],
                            [
                                'height' => 64,
                                'width' => 64,
                                'url' => 'https://i.scdn.co/image/ab67616d00004851e71c53b1e7e74ddcaa427755',
                            ],
                        ],
                    ],
                    'url' => 'https://open.spotify.com/album/1OM6ULzT778hgqBI4stbFR',
                    'vendor' => 'spotify',
                    'vendor_id' => '1OM6ULzT778hgqBI4stbFR',
                ],
                'isrc' => 'GBBLG0100312',
                'url' => 'https://open.spotify.com/track/6tvtFyEdNpeurBkT2zNMEL',
                'vendor' => 'spotify',
                'vendor_id' => '6tvtFyEdNpeurBkT2zNMEL',
                'meta' => [
                    'popularity' => 55,
                    'available_markets' => ['AD', 'AE', 'AL', 'AR', 'AT', 'AU', 'BA', 'BE', 'BG', 'BH', 'BO', 'BR', 'BY', 'CA', 'CH', 'CL', 'CO', 'CR', 'CY', 'CZ', 'DE', 'DK', 'DO', 'DZ', 'EC', 'EE', 'EG', 'ES', 'FI', 'FR', 'GB', 'GR', 'GT', 'HK', 'HN', 'HR', 'HU', 'ID', 'IE', 'IL', 'IS', 'IT', 'JO', 'JP', 'KW', 'KZ', 'LB', 'LI', 'LT', 'LU', 'LV', 'MA', 'MC', 'MD', 'ME', 'MK', 'MT', 'MX', 'MY', 'NI', 'NL', 'NO', 'NZ', 'OM', 'PA', 'PE', 'PH', 'PL', 'PS', 'PT', 'PY', 'QA', 'RO', 'RS', 'RU', 'SA', 'SE', 'SG', 'SI', 'SK', 'SV', 'TH', 'TN', 'TR', 'TW', 'UA', 'US', 'UY', 'VN', 'XK', 'ZA'],
                ],
                'pivot' => [
                    'order' => 0,
                    'added_at' => '2017-03-17 10:39:04',
                    'meta' => [
                        'is_local' => false,
                        'added_by' => 'archy_bold',
                        'added_by_url' => 'https://open.spotify.com/user/archy_bold',
                    ],
                ],
                'uri' => 'spotify:track:6tvtFyEdNpeurBkT2zNMEL',
            ],
            [
                'title' => 'I Heard It Through The Grapevine',
                'artists' => 'Marvin Gaye',
                'album' => [
                    'name' => 'I Heard It Through The Grapevine / In The Groove (Stereo)',
                    'release_date' => '1969-08-26',
                    'release_date_str' => '1969-08-26',
                    'release_date_precision' => 'day',
                    'meta' => [
                        'available_markets' => [],
                        'images' => [
                            [
                                'height' => 640,
                                'width' => 640,
                                'url' => 'https://i.scdn.co/image/ab67616d0000b27360f5e8d519591d51e533e822',
                            ],
                            [
                                'height' => 300,
                                'width' => 300,
                                'url' => 'https://i.scdn.co/image/ab67616d00001e0260f5e8d519591d51e533e822',
                            ],
                            [
                                'height' => 64,
                                'width' => 64,
                                'url' => 'https://i.scdn.co/image/ab67616d0000485160f5e8d519591d51e533e822',
                            ],
                        ],
                    ],
                    'url' => 'https://open.spotify.com/album/1jcnZvZWvAGzNyQ7GNVy8X',
                    'vendor' => 'spotify',
                    'vendor_id' => '1jcnZvZWvAGzNyQ7GNVy8X',
                ],
                'isrc' => 'USMO16884718',
                'url' => 'https://open.spotify.com/track/27m1soUndRthrAA1ediOXn',
                'vendor' => 'spotify',
                'vendor_id' => '27m1soUndRthrAA1ediOXn',
                'meta' => [
                    'popularity' => 3,
                    'available_markets' => [],
                ],
                'pivot' => [
                    'order' => 1,
                    'added_at' => '2017-03-17 10:41:00',
                    'meta' => [
                        'is_local' => false,
                        'added_by' => 'archy_bold',
                        'added_by_url' => 'https://open.spotify.com/user/archy_bold',
                    ],
                ],
                'uri' => 'spotify:track:27m1soUndRthrAA1ediOXn',
            ],
            [
                'title' => 'Groove Me',
                'artists' => 'King Floyd',
                'album' => [
                    'name' => 'We Are Marshall Soundtrack',
                    'release_date' => '2007-09-15',
                    'release_date_str' => '2007-09',
                    'release_date_precision' => 'month',
                    'meta' => [
                        'available_markets' => [],
                        'images' => [
                            [
                                'height' => 640,
                                'width' => 640,
                                'url' => 'https://i.scdn.co/image/ab67616d0000b273be2528999e9ae5c1928ae8b2',
                            ],
                            [
                                'height' => 300,
                                'width' => 300,
                                'url' => 'https://i.scdn.co/image/ab67616d00001e02be2528999e9ae5c1928ae8b2',
                            ],
                            [
                                'height' => 64,
                                'width' => 64,
                                'url' => 'https://i.scdn.co/image/ab67616d00004851be2528999e9ae5c1928ae8b2',
                            ],
                        ],
                    ],
                    'url' => 'https://open.spotify.com/album/72gsfXVb92BPzas9adrQ1x',
                    'vendor' => 'spotify',
                    'vendor_id' => '72gsfXVb92BPzas9adrQ1x',
                ],
                'isrc' => 'USWR50428155',
                'url' => 'https://open.spotify.com/track/32aSj7Bo0rl3s5fwppu5VP',
                'vendor' => 'spotify',
                'vendor_id' => '32aSj7Bo0rl3s5fwppu5VP',
                'meta' => [
                    'popularity' => 0,
                    'available_markets' => [],
                ],
                'pivot' => [
                    'order' => 2,
                    'added_at' => '2017-03-17 10:41:00',
                    'meta' => [
                        'is_local' => false,
                        'added_by' => 'archy_bold',
                        'added_by_url' => 'https://open.spotify.com/user/archy_bold',
                    ],
                ],
                'uri' => 'spotify:track:32aSj7Bo0rl3s5fwppu5VP',
            ],
            [
                'title' => 'Raspberry Beret',
                'artists' => 'Prince',
                'album' => [
                    'name' => 'Raspberry Beret / She\'s Always In My Hair',
                    'release_date' => '1985-01-01',
                    'release_date_str' => '1985-01-01',
                    'release_date_precision' => 'day',
                    'meta' => [
                        'available_markets' => ['AD', 'AE', 'AL', 'AR', 'AT', 'AU', 'BA', 'BE', 'BG', 'BH', 'BO', 'BR', 'BY', 'CA', 'CH', 'CL', 'CO', 'CR', 'CY', 'CZ', 'DE', 'DK', 'DO', 'DZ', 'EC', 'EE', 'EG', 'ES', 'FI', 'FR', 'GB', 'GR', 'GT', 'HK', 'HN', 'HR', 'HU', 'ID', 'IE', 'IL', 'IN', 'IS', 'IT', 'JO', 'JP', 'KW', 'KZ', 'LB', 'LI', 'LT', 'LU', 'LV', 'MA', 'MC', 'MD', 'ME', 'MK', 'MT', 'MX', 'MY', 'NI', 'NL', 'NO', 'NZ', 'OM', 'PA', 'PE', 'PH', 'PL', 'PS', 'PT', 'PY', 'QA', 'RO', 'RS', 'RU', 'SA', 'SE', 'SG', 'SI', 'SK', 'SV', 'TH', 'TN', 'TR', 'TW', 'UA', 'US', 'UY', 'VN', 'XK', 'ZA'],
                        'images' => [
                            [
                                'height' => 640,
                                'width' => 640,
                                'url' => 'https://i.scdn.co/image/ab67616d0000b2735b2718419ed80b4da4b1b96f',
                            ],
                            [
                                'height' => 300,
                                'width' => 300,
                                'url' => 'https://i.scdn.co/image/ab67616d00001e025b2718419ed80b4da4b1b96f',
                            ],
                            [
                                'height' => 64,
                                'width' => 64,
                                'url' => 'https://i.scdn.co/image/ab67616d000048515b2718419ed80b4da4b1b96f',
                            ],
                        ],
                    ],
                    'url' => 'https://open.spotify.com/album/2mteIXvdn40yXE54VXncCY',
                    'vendor' => 'spotify',
                    'vendor_id' => '2mteIXvdn40yXE54VXncCY',
                ],
                'isrc' => 'USWB19902876',
                'url' => 'https://open.spotify.com/track/4W9zWGHdrDFygC2Mf1Xmru',
                'vendor' => 'spotify',
                'vendor_id' => '4W9zWGHdrDFygC2Mf1Xmru',
                'meta' => [
                    'popularity' => 40,
                    'available_markets' => ['AD', 'AE', 'AL', 'AR', 'AT', 'AU', 'BA', 'BE', 'BG', 'BH', 'BO', 'BR', 'BY', 'CA', 'CH', 'CL', 'CO', 'CR', 'CY', 'CZ', 'DE', 'DK', 'DO', 'DZ', 'EC', 'EE', 'EG', 'ES', 'FI', 'FR', 'GB', 'GR', 'GT', 'HK', 'HN', 'HR', 'HU', 'ID', 'IE', 'IL', 'IN', 'IS', 'IT', 'JO', 'JP', 'KW', 'KZ', 'LB', 'LI', 'LT', 'LU', 'LV', 'MA', 'MC', 'MD', 'ME', 'MK', 'MT', 'MX', 'MY', 'NI', 'NL', 'NO', 'NZ', 'OM', 'PA', 'PE', 'PH', 'PL', 'PS', 'PT', 'PY', 'QA', 'RO', 'RS', 'RU', 'SA', 'SE', 'SG', 'SI', 'SK', 'SV', 'TH', 'TN', 'TR', 'TW', 'UA', 'US', 'UY', 'VN', 'XK', 'ZA'],
                ],
                'pivot' => [
                    'order' => 3,
                    'added_at' => '2017-03-17 10:41:00',
                    'meta' => [
                        'is_local' => false,
                        'added_by' => 'archy_bold',
                        'added_by_url' => 'https://open.spotify.com/user/archy_bold',
                    ],
                ],
                'uri' => 'spotify:track:4W9zWGHdrDFygC2Mf1Xmru',
            ],
        ];
    }

    public function getExpectedUserPlaylists()
    {
        return [
            [
                'name' => 'Ridonculous',
                'url' => 'https://open.spotify.com/playlist/19DAgMGSIyeUBEm6a9MTNg',
                'vendor' => 'spotify',
                'vendor_id' => '19DAgMGSIyeUBEm6a9MTNg',
                'public' => true,
                'description' => 'Only the best tunes',
                'owner_id' => null,
                'meta' => [
                    'collaborative' => false,
                    'images' => [
                        [
                            'height' => 640,
                            'width' => 640,
                            'url' => 'https://mosaic.scdn.co/640/ab67616d0000b2733e45154c8f34df3fe2d60b44ab67616d0000b2735b2718419ed80b4da4b1b96fab67616d0000b273aff6573c5110e0732fbab3d8ab67616d0000b273e71c53b1e7e74ddcaa427755',
                        ],
                        [
                            'height' => 300,
                            'width' => 300,
                            'url' => 'https://mosaic.scdn.co/300/ab67616d0000b2733e45154c8f34df3fe2d60b44ab67616d0000b2735b2718419ed80b4da4b1b96fab67616d0000b273aff6573c5110e0732fbab3d8ab67616d0000b273e71c53b1e7e74ddcaa427755',
                        ],
                        [
                            'height' => 60,
                            'width' => 60,
                            'url' => 'https://mosaic.scdn.co/60/ab67616d0000b2733e45154c8f34df3fe2d60b44ab67616d0000b2735b2718419ed80b4da4b1b96fab67616d0000b273aff6573c5110e0732fbab3d8ab67616d0000b273e71c53b1e7e74ddcaa427755',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Genosys never happened 2020',
                'url' => 'https://open.spotify.com/playlist/2YukjLklW9M2mEcw1MsPrc',
                'vendor' => 'spotify',
                'vendor_id' => '2YukjLklW9M2mEcw1MsPrc',
                'public' => true,
                'description' => '',
                'owner_id' => null,
                'meta' => [
                    'collaborative' => false,
                    'images' => [
                        [
                            'height' => 640,
                            'width' => 640,
                            'url' => 'https://mosaic.scdn.co/640/ab67616d0000b27309be68e816f1285d903c8d00ab67616d0000b27384b8c7f8ed7ae034d05cf026ab67616d0000b273c0a21d05e01c53ffa8fc0d4bab67616d0000b273cee8634e7ae8d0592dd3b3dc',
                        ],
                        [
                            'height' => 300,
                            'width' => 300,
                            'url' => 'https://mosaic.scdn.co/300/ab67616d0000b27309be68e816f1285d903c8d00ab67616d0000b27384b8c7f8ed7ae034d05cf026ab67616d0000b273c0a21d05e01c53ffa8fc0d4bab67616d0000b273cee8634e7ae8d0592dd3b3dc',
                        ],
                        [
                            'height' => 60,
                            'width' => 60,
                            'url' => 'https://mosaic.scdn.co/60/ab67616d0000b27309be68e816f1285d903c8d00ab67616d0000b27384b8c7f8ed7ae034d05cf026ab67616d0000b273c0a21d05e01c53ffa8fc0d4bab67616d0000b273cee8634e7ae8d0592dd3b3dc',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'blm',
                'url' => 'https://open.spotify.com/playlist/0LlbPNvjlDJe2L8oUkZ7Pt',
                'vendor' => 'spotify',
                'vendor_id' => '0LlbPNvjlDJe2L8oUkZ7Pt',
                'public' => true,
                'description' => '',
                'owner_id' => null,
                'meta' => [
                    'collaborative' => false,
                    'images' => [
                        [
                            'height' => 640,
                            'width' => 640,
                            'url' => 'https://i.scdn.co/image/ab67616d0000b2738724b960ec88564f05ed55b2',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Misheard Lyrics',
                'url' => 'https://open.spotify.com/playlist/2T6Jo89FVoZpJDlebUNWVO',
                'vendor' => 'spotify',
                'vendor_id' => '2T6Jo89FVoZpJDlebUNWVO',
                'public' => true,
                'description' => '1. Peas, pies, pudding, chips and riiii-ee-iiii-ee-ice. 2. Pikachu',
                'owner_id' => null,
                'meta' => [
                    'collaborative' => false,
                    'images' => [
                        [
                            'height' => 640,
                            'width' => 640,
                            'url' => 'https://i.scdn.co/image/ab67616d0000b273c775772fa7bbd72e6565b85c',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getExpectedAudioFeatures()
    {
        return [
            'type' => 'audio_features',
            'vendor' => 'spotify',
            'meta' => [
                'danceability' => 0.691,
                'energy' => 0.762,
                'key' => 11,
                'loudness' => -6.470,
                'mode' => 0,
                'speechiness' => 0.0367,
                'acousticness' => 0.462,
                'instrumentalness' => 0.0286,
                'liveness' => 0.0804,
                'valence' => 0.957,
                'tempo' => 128.111,
                'duration_ms' => 187533,
                'time_signature' => 4,
            ],
            'camelot_code' => '10A',
            'duration_s' => 187.533,
        ];
    }

    public function getSpotifyPlaylist()
    {
        return $this->readJsonTestData('get-playlist-success.json');
    }

    public function getSpotifyPlaylistTracks($page = 1)
    {
        if ($page == 1) {
            return $this->readJsonTestData('get-playlist-tracks-page-1-success.json');
        }
        else {
            return $this->readJsonTestData('get-playlist-tracks-page-2-success.json');
        }
    }

    public function getSpotifyUserPlaylists($page = 1)
    {
        if ($page == 1) {
            return $this->readJsonTestData('get-user-playlists-page-1-success.json');
        }
        else {
            return $this->readJsonTestData('get-user-playlists-page-2-success.json');
        }
    }

    public function getSpotifyAllUserPlaylists()
    {
        return [
            'href' => 'https://api.spotify.com/v1/users/archy_bold/playlists?offset=0&limit=2',
            'items' => array_merge(
                $this->getSpotifyUserPlaylists(1)['items'],
                $this->getSpotifyUserPlaylists(2)['items']
            ),
            'limit' => 2,
            'next' => 'https://api.spotify.com/v1/users/archy_bold/playlists?offset=2&limit=2',
            'offset' => 0,
            'previous' => null,
            'total' => 4
        ];
    }

    public function getSpotifyAudioFeatures()
    {
        return $this->readJsonTestData('get-audio-features-success.json');
    }

    public function getSpotifyCreatePlaylist()
    {
        return $this->readJsonTestData('create-playlist-success.json');
    }

    public function getSpotifyAddPlaylistTracks()
    {
        return $this->readJsonTestData('add-playlist-tracks-success.json');
    }

    public function readJsonTestData($filename)
    {
        return json_decode(file_get_contents(realpath(dirname(__FILE__).'/../fixtures/spotify/' . $filename)), true);
    }
}
