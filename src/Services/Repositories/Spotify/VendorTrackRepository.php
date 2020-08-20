<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Spotify;

use ArchyBold\LaravelMusicServices\Services\Contracts\VendorService;
use ArchyBold\LaravelMusicServices\Services\Repositories\Contracts\VendorTrackRepository as RepositoryInterface;
use ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent\VendorTrackRepository as ParentRepository;

class VendorTrackRepository extends ParentRepository implements RepositoryInterface
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
     * Maps the vendor audio features to a TrackInformation attributes array.
     *
     * @param array $vendorPlaylist
     * @return array
     */
    protected function mapVendorAudioFeaturesToAttributes($vendorAudioFeatures)
    {
        return [
            'vendor' => $this->getVendor(),
            'meta' => collect($vendorAudioFeatures)->only([
                'danceability',
                'energy',
                'key',
                'loudness',
                'mode',
                'speechiness',
                'acousticness',
                'instrumentalness',
                'liveness',
                'valence',
                'tempo',
                'duration_ms',
                'time_signature',
            ]),
        ];
    }
}
