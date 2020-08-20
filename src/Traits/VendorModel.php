<?php

namespace ArchyBold\LaravelMusicServices\Traits;

use ArchyBold\LaravelMusicServices\VendorAlbum;
use ArchyBold\LaravelMusicServices\VendorPlaylist;
use ArchyBold\LaravelMusicServices\VendorTrack;
use ArchyBold\LaravelMusicServices\VendorUser;

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
                case VendorAlbum::class:
                    return 'spotify:album:' . $this->vendor_id;
                case VendorPlaylist::class:
                    return 'spotify:playlist:' . $this->vendor_id;
                case VendorTrack::class:
                    return 'spotify:track:' . $this->vendor_id;
                case VendorUser::class:
                    return 'spotify:user:' . $this->vendor_id;
            }
        }
        return $this->vendor_id;
    }
}
