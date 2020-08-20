<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent;

use ArchyBold\LaravelMusicServices\TrackInformation;
use ArchyBold\LaravelMusicServices\Repositories\Repository;
use ArchyBold\LaravelMusicServices\Track;
use ArchyBold\LaravelMusicServices\Services\Contracts\VendorService;

abstract class VendorTrackRepository extends Repository
{
    /**
     * The entity for this repository.
     *
     * @var string $entity
     */
    protected $entity = VendorTrack::class;

    /** @var VendorService */
    protected $service;
    /** @var boolean */
    protected $authenticated = false;

    /**
     * @param VendorService $service
     */
    public function __construct(VendorService $service)
    {
        $this->service = $service;
    }

    protected function authenticate()
    {
        if (!$this->authenticated) {
            $this->service->authenticate();
            $this->authenticated = true;
        }
    }

    /**
     * Get a track's audio features from an external vendor, store them and return the TrackInformation object.
     *
     * @param string $id
     * @return \App\TrackInformation
     */
    public function getAudioFeatures($id)
    {
        $this->authenticate();
        $id = $this->service->parseId($id, 'track');

        // Get the track information and map to attributes
        $vendorAudioFeatures = $this->service->getTrackAudioFeatures($id);
        // Get the track.
        $track = VendorTrack::vendorFind($this->getVendor(), $id)->first();

        if ($track && !is_null($vendorAudioFeatures) && !empty($vendorAudioFeatures)) {
            // Map the attributes.
            $audioFeaturesAttrs = $this->mapVendorAudioFeaturesToAttributes($vendorAudioFeatures);
            $audioFeaturesAttrs['type'] = TrackInformation::AUDIO_FEATURES;
            $audioFeaturesAttrs['vendor_track_id'] = $track->id;

            // Next check if the information already exists.
            $audioFeatures = $track->trackInformation()->audioFeatures($this->getVendor())->first();

            // Now either update the existing track information or create a new one.
            if (is_null($audioFeatures)) {
                $audioFeatures = TrackInformation::create($audioFeaturesAttrs);
            }
            else {
                $audioFeatures->update($audioFeaturesAttrs);
            }

            return $audioFeatures->fresh();
        }
        return null;
    }

    /**
     * Get the vendor string eg 'spotify'
     *
     * @return string
     */
    abstract public function getVendor();

    /**
     * Maps the vendor audio features to a TrackInformation attributes array.
     *
     * @param array $vendorAudioFeatures
     * @return array
     */
    abstract protected function mapVendorAudioFeaturesToAttributes($vendorAudioFeatures);
}
