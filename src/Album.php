<?php

namespace ArchyBold\LaravelMusicServices;

use ArchyBold\LaravelMusicServices\Traits\VendorModel;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use VendorModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'artists',
        'upc',
        'release_date',
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

    public function getTable()
    {
        return config('music-services.table_names.albums', parent::getTable());
    }

    /**
     * Get the tracks for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tracks()
    {
        return $this->hasMany(
            config('music-services.models.track', Track::class),
            'album_id'
        );
    }
}
