<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent;

use ArchyBold\LaravelMusicServices\TrackInformation;
use ArchyBold\LaravelMusicServices\Repositories\Repository;
use ArchyBold\LaravelMusicServices\Track;
use ArchyBold\LaravelMusicServices\Services\Contracts\VendorService;

abstract class TrackRepository extends Repository
{
    /**
     * The entity for this repository.
     *
     * @var string $entity
     */
    protected $entity = null;

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
        $this->entity = config('music-services.models.track', Track::class);
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
        $trackInformationClass = config('music-services.models.track_information', TrackInformation::class);
        $id = $this->service->parseId($id, 'track');

        // Get the track information and map to attributes
        $serviceAudioFeatures = $this->service->getTrackAudioFeatures($id);
        // Get the track.
        $track = $this->entity::vendorFind($this->getVendor(), $id)->first();

        if ($track && !is_null($serviceAudioFeatures) && !empty($serviceAudioFeatures)) {
            // Map the attributes.
            $audioFeaturesAttrs = $this->mapServiceAudioFeaturesToAttributes($serviceAudioFeatures);
            $audioFeaturesAttrs['type'] = TrackInformation::AUDIO_FEATURES;
            $audioFeaturesAttrs['track_id'] = $track->id;

            // Next check if the information already exists.
            $audioFeatures = $track->trackInformation()->audioFeatures($this->getVendor())->first();

            // Now either update the existing track information or create a new one.
            if (is_null($audioFeatures)) {
                $audioFeatures = $trackInformationClass::create($audioFeaturesAttrs);
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
     * Maps the service audio features to a TrackInformation attributes array.
     *
     * @param array $serviceAudioFeatures
     * @return array
     */
    abstract protected function mapServiceAudioFeaturesToAttributes($serviceAudioFeatures);
}
