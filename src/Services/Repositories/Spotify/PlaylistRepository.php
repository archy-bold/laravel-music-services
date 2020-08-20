<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Spotify;

use Carbon\Carbon;
use ArchyBold\LaravelMusicServices\Services\Contracts\VendorService;
use ArchyBold\LaravelMusicServices\Services\Repositories\Contracts\PlaylistRepository as RepositoryInterface;
use ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent\PlaylistRepository as ParentRepository;

class PlaylistRepository extends ParentRepository implements RepositoryInterface
{
    public function __construct(VendorService $service)
    {
        parent::__construct($service);
    }

    /**
     * Get the vendor string eg 'spotify'
     *
     * @return string
     */
    public function getVendor()
    {
        return 'spotify';
    }

    /**
     * Maps the vendor playlist to a Playlist attributes array.
     *
     * @param array $playlist
     * @return array
     */
    public function mapServicePlaylistToAttributes($playlist)
    {
        return [
            'name' => $playlist['name'] ?? '',
            'url' => $playlist['external_urls']['spotify'] ?? null,
            'vendor' => $this->getVendor(),
            'vendor_id' => $playlist['id'] ?? '',
            'public' => $playlist['public'] ?? null,
            'description' => $playlist['description'] ?? null,
            'playlist_id' => null,
            'owner_id' => null,
            'meta' => collect($playlist)->only(['collaborative', 'images']),
        ];
    }

    /**
     * Maps the vendor playlist to a PlaylistSnapshot attributes array.
     *
     * @param array $playlist
     * @return array
     */
    protected function mapServicePlaylistToSnapshotAttributes($playlist)
    {
        return [
            'num_followers' => $playlist['followers']['total'] ?? null,
            'meta' => [],
        ];
    }

    /**
     * Maps the user vendor playlists to arrays.
     *
     * @param array $playlists
     * @return array
     */
    protected function mapServiceUserPlaylistsToArray($playlists)
    {
        if (!isset($playlists['items'])) {
            return [];
        }
        return $playlists['items'];
    }

    /**
     * Maps the vendor playlist to a VendorUser attributes array.
     *
     * @param array $playlist
     * @return array
     */
    protected function mapServicePlaylistToUserAttributes($playlist)
    {
        return [
            'name' => $playlist['owner']['display_name'] ?? '',
            'meta' => [],
            'url' => $playlist['owner']['external_urls']['spotify'] ?? null,
            'vendor' => $this->getVendor(),
            'vendor_id' => $playlist['owner']['id'] ?? null,
        ];
    }

    /**
     * Maps the vendor playlist tracks to Vendor Track attributes arrays.
     *
     * @param array $Tracks
     * @return array
     */
    protected function mapServicePlaylistTracksToAttributes($Tracks)
    {
        if (!isset($Tracks['items'])) {
            return [];
        }
        return collect($Tracks['items'])->map(function ($item) {
            $retval = [
                'vendor' => $this->getVendor(),
                'pivot' => [
                    'added_at' => array_key_exists('added_at', $item) ? new Carbon($item['added_at']) : null,
                    'meta' => [
                        'is_local' => $item['is_local'] ?? null,
                        'added_by' => $item['added_by']['id'] ?? null,
                        'added_by_url' => $item['added_by']['external_urls']['spotify'] ?? null,
                    ],
                ],
            ];
            if (array_key_exists('track', $item) && !is_null($item['track'])) {
                $artists = null;
                if (array_key_exists('artists', $item['track'])) {
                    $artists = array_map(function ($artist) {
                        return $artist['name'] ?? '';
                    }, $item['track']['artists']);
                    $artists = implode(', ', $artists);
                }

                $retval = array_merge($retval, [
                    'title' => $item['track']['name'] ?? null,
                    'artists' => $artists,
                    'album' => $this->mapAlbumToAttrs($item['track']['album'] ?? []),
                    'isrc' => $item['track']['external_ids']['isrc'] ?? null,
                    'url' => $item['track']['external_urls']['spotify'] ?? null,
                    'vendor_id' => $item['track']['id'] ?? null,
                    'meta' => [
                        'available_markets' => $item['track']['available_markets'] ?? [],
                        'popularity' => $item['track']['popularity'] ?? null,
                    ],
                ]);
            }
            return $retval;
        })->filter(function ($track) {
            return isset($track['vendor_id']);
        });
    }

    /**
     * Function to map a Spotify album to attributes.
     *
     * @param array $album
     * @return array
     */
    protected function mapAlbumToAttrs($album)
    {
        $retval = [
            'name' => $album['name'] ?? '',
            'release_date' => null,
            'release_date_str' => $album['release_date'] ?? '',
            'release_date_precision' => $album['release_date_precision'] ?? '',
            'meta' => collect($album)->only(['images', 'available_markets']),
            'url' => $album['external_urls']['spotify'] ?? null,
            'vendor' => $this->getVendor(),
            'vendor_id' => $album['id'] ?? null,
        ];
        switch ($retval['release_date_precision']) {
            case 'day':
                $retval['release_date'] = $retval['release_date_str'];
                break;
            case 'month':
                $retval['release_date'] = $retval['release_date_str'] . '-15';
                break;
            case 'year':
                $retval['release_date'] = $retval['release_date_str'] . '-06-01';
                break;
        }
        return $retval;
    }

    /**
     * Get the ISRC from a vendor track.
     *
     * @param array $Track
     * @return array
     */
    protected function getIsrcFromTrack($Track)
    {
        return $Track['external_ids']['isrc'] ?? null;
    }
}
