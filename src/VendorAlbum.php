<?php

namespace ArchyBold\LaravelMusicServices;

use ArchyBold\LaravelMusicServices\Traits\VendorModel;
use Illuminate\Database\Eloquent\Model;

class VendorAlbum extends Model
{
    use VendorModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'release_date',
        'release_date_str',
        'release_date_precision',
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
        'release_date' => 'datetime:Y-m-d',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
	 */
    protected $dates = [
        'release_date',
    ];

    /**
     * Get the tracks for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tracks()
    {
        return $this->hasMany(VendorTrack::class, 'album_id');
    }
}
