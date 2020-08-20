<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Contracts;

interface VendorTrackRepository
{
    // Custom repository

    /**
     * Get a track's audio features from an external vendor, store them and return the TrackInformation object.
     *
     * @param string $id
     * @return \App\TrackInformation
     */
    public function getAudioFeatures($id);
}
