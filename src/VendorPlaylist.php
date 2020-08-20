<?php

namespace ArchyBold\LaravelMusicServices;

use ArchyBold\LaravelMusicServices\Traits\VendorModel;
use Illuminate\Database\Eloquent\Model;

class VendorPlaylist extends Model
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

    /**
     * Get the playlist for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }

    /**
     * Get the owner for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(VendorUser::class);
    }

    /**
     * Get the snapshots for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function snapshots()
    {
        return $this->hasMany(PlaylistSnapshot::class);
    }

    /**
     * Get the latestSnapshot for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestSnapshot()
    {
        return $this->hasOne(PlaylistSnapshot::class)->latest();
    }
}
