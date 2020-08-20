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
            switch ($class) {
                case Album::class:
                    return 'spotify:album:' . $this->vendor_id;
                case Playlist::class:
                    return 'spotify:playlist:' . $this->vendor_id;
                case Track::class:
                    return 'spotify:track:' . $this->vendor_id;
                case User::class:
                    return 'spotify:user:' . $this->vendor_id;
            }
        }
        return $this->vendor_id;
    }
}
