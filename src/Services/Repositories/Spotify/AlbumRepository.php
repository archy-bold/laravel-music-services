<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Spotify;

use ArchyBold\LaravelMusicServices\Services\Contracts\VendorService;
use ArchyBold\LaravelMusicServices\Services\Repositories\Contracts\AlbumRepository as RepositoryInterface;
use ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent\AlbumRepository as ParentRepository;

class AlbumRepository extends ParentRepository implements RepositoryInterface
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
    public static function getVendor()
    {
        return 'spotify';
    }

    /**
     * Maps the vendor audio features to a AlbumInformation attributes array.
     *
     * @param array $playlist
     * @return array
     */
    public static function mapServiceAlbumToAttributes($serviceAlbum)
    {
        // Collect the artists
        $artists = '';
        if (array_key_exists('artists', $serviceAlbum)) {
            $artists = array_map(function ($artist) {
                return $artist['name'] ?? '';
            }, $serviceAlbum['artists']);
            $artists = implode(', ', $artists);
        }

        $retval = [
            'name' => $serviceAlbum['name'] ?? '',
            'artists' => $artists,
            'upc' => $serviceAlbum['external_ids']['upc'] ?? null,
            'type' => $serviceAlbum['album_type'] ?? 'album',
            'release_date' => null,
            'meta' => collect($serviceAlbum)->only([
                'popularity',
                'release_date',
                'release_date_precision',
                'images',
                'available_markets',
                'genres',
                'label',
            ])->toArray(),
            'url' => $serviceAlbum['external_urls']['spotify'] ?? null,
            'vendor' => self::getVendor(),
            'vendor_id' => $serviceAlbum['id'] ?? null,
        ];
        // Set the release date based on the given precision
        if (isset($retval['meta']['release_date_precision'])) {
            switch ($retval['meta']['release_date_precision']) {
                case 'day':
                    $retval['release_date'] = $retval['meta']['release_date'];
                    break;
                case 'month':
                    $retval['release_date'] = $retval['meta']['release_date'] . '-15';
                    break;
                case 'year':
                    $retval['release_date'] = $retval['meta']['release_date'] . '-06-01';
                    break;
            }
        }
        // Add the copyrights, if available.
        if (array_key_exists('copyrights', $serviceAlbum)) {
            foreach ($serviceAlbum['copyrights'] as $copyright) {
                if ($copyright['type'] == 'C') {
                    $retval['meta']['c_copyright'] = $copyright['text'];
                }
                if ($copyright['type'] == 'P') {
                    $retval['meta']['p_copyright'] = $copyright['text'];
                }
            }
        }
        return $retval;
    }
}
