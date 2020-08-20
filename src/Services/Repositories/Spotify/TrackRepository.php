<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Spotify;

use ArchyBold\LaravelMusicServices\Services\Contracts\VendorService;
use ArchyBold\LaravelMusicServices\Services\Repositories\Contracts\TrackRepository as RepositoryInterface;
use ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent\TrackRepository as ParentRepository;

class TrackRepository extends ParentRepository implements RepositoryInterface
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
     * @param array $playlist
     * @return array
     */
    protected function mapServiceAudioFeaturesToAttributes($serviceAudioFeatures)
    {
        return [
            'vendor' => $this->getVendor(),
            'meta' => collect($serviceAudioFeatures)->only([
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
