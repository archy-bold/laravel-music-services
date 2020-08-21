<?php

namespace ArchyBold\LaravelMusicServices;

use ArchyBold\LaravelMusicServices\Traits\VendorModel;
use Illuminate\Database\Eloquent\Model;

class User extends Model
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

    public function getTable()
    {
        return config('music-services.table_names.users', parent::getTable());
    }

    /**
     * Get the playlists for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function playlists()
    {
        return $this->hasMany(
            config('music-services.models.playlist', Playlist::class),
            'owner_id'
        );
    }
}
