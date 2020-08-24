<?php

namespace ArchyBold\LaravelMusicServices\Traits;

use ArchyBold\LaravelMusicServices\Album;
use ArchyBold\LaravelMusicServices\Playlist;
use ArchyBold\LaravelMusicServices\Track;
use ArchyBold\LaravelMusicServices\User;

trait VendorModel
{
    /**
     * Scope to get the current snapshots.
     */
    public function scopeVendorFind($query, $vendor, $vendorId)
    {
        // Get the IDs of all latest snapshots.
        return $query->whereVendor($vendor)
            ->whereVendorId($vendorId)
            ->limit(1);
    }

    /**
     * Get the uri attribute.
     *
     * @return string
     */
    public function getUriAttribute()
    {
        if ($this->vendor == 'spotify' && !is_null($this->vendor_id)) {
            $class = get_class($this);
            $albumClass = config('music-services.models.album', Album::class);
            $playlistClass = config('music-services.models.playlist', Playlist::class);
            $trackClass = config('music-services.models.track', Track::class);
            $userClass = config('music-services.models.user', User::class);
            switch ($class) {
                case $albumClass:
                    return 'spotify:album:' . $this->vendor_id;
                case $playlistClass:
                    return 'spotify:playlist:' . $this->vendor_id;
                case $trackClass:
                    return 'spotify:track:' . $this->vendor_id;
                case $userClass:
                    return 'spotify:user:' . $this->vendor_id;
            }
        }
        return $this->vendor_id;
    }
}
