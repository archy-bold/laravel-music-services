<?php

namespace ArchyBold\LaravelMusicServices;

use ArchyBold\LaravelMusicServices\Traits\VendorModel;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
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
        'public',
        'description',
        'meta',
        'owner_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'public' => 'boolean',
        'meta' => 'array',
    ];

    public function getTable()
    {
        return config('music-services.table_names.playlists', parent::getTable());
    }

    /**
     * Get the owner for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(
            config('music-services.models.user', User::class),
            'owner_id'
        );
    }

    /**
     * Get the snapshots for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function snapshots()
    {
        return $this->hasMany(
            config('music-services.models.playlist_snapshot', PlaylistSnapshot::class),
            'playlist_id'
        );
    }

    /**
     * Get the latestSnapshot for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestSnapshot()
    {
        return $this->hasOne(
            config('music-services.models.playlist_snapshot', PlaylistSnapshot::class),
            'playlist_id'
        )->latest();
    }
}
