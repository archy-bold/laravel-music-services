<?php

namespace ArchyBold\LaravelMusicServices;

use ArchyBold\LaravelMusicServices\Traits\VendorModel;
use Illuminate\Database\Eloquent\Model;

class VendorUser extends Model
{
    use VendorModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
        'vendor',
        'vendor_id',
        'meta',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Get the vendorPlaylists for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vendorPlaylists()
    {
        return $this->hasMany(VendorPlaylist::class, 'owner_id');
    }
}
