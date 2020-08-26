<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent;

use ArchyBold\LaravelMusicServices\Album;
use ArchyBold\LaravelMusicServices\Repositories\Repository;
use ArchyBold\LaravelMusicServices\Services\Contracts\VendorService;

abstract class AlbumRepository extends Repository
{
    /**
     * The entity for this repository.
     *
     * @var string $entity
     */
    protected $entity = null;

    /**
     * The vendor service.
     *
     * @var VendorService $service
     */
    public $service;

    /** @var boolean */
    protected $authenticated = false;

    /**
     * @param VendorService $service
     */
    public function __construct(VendorService $service)
    {
        $this->service = $service;
        $this->entity = config('music-services.models.album', Album::class);
    }

    protected function authenticate()
    {
        if (!$this->authenticated) {
            $this->service->authenticate();
            $this->authenticated = true;
        }
    }

    /**
     * Get an album from an external vendor, store it and return the object.
     *
     * @param string $id
     * @return \ArchyBold\LaravelMusicServices\Album
     */
    public function get($id)
    {
        $this->authenticate();
        $id = $this->service->parseId($id, 'album');

        $serviceAlbum = $this->service->getAlbum($id);
        return $this->createModels($serviceAlbum, $id);
    }

    /**
     * Get the vendor string eg 'spotify'
     *
     * @return string
     */
    abstract public static function getVendor();

    /**
     * Maps the service album to an Album attributes array.
     *
     * @param array $serviceAudioFeatures
     * @return array
     */
    abstract public static function mapServiceAlbumToAttributes($serviceAlbum);

    /**
     * Function to generate the models for a service album.
     *
     * @param array $serviceAlbum
     * @param string $id
     * @return ArchyBold\LaravelMusicServices\Album|null
     */
    protected function createModels($serviceAlbum, $id)
    {
        if (!is_null($serviceAlbum) && !empty($serviceAlbum)) {
            // Map the attributes.
            $attrs = static::mapServiceAlbumToAttributes($serviceAlbum);

            // Next check if the album already exists.
            $album = $this->entity::vendorFind($this->getVendor(), $id)->first();

            // Now either update the existing album or create a new one.
            if (is_null($album)) {
                $album = $this->entity::create($attrs);
            }
            else {
                $album->update($attrs);
            }

            return $album->fresh();
        }
        return null;
    }
}
