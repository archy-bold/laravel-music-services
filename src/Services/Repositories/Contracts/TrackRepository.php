<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Contracts;

interface TrackRepository
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
