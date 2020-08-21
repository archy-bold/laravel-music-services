<?php

namespace ArchyBold\LaravelMusicServices;

use ArchyBold\LaravelMusicServices\Traits\VendorModel;
use ArchyBold\LaravelMusicServices\Pivots\PlaylistTrackPivot;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use VendorModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'artists',
        'album',
        'isrc',
        'url',
        'vendor',
        'vendor_id',
        'meta',
        'album_id',
        'track_id',
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'uri',
    ];

    public function getTable()
    {
        return config('music-services.table_names.tracks', parent::getTable());
    }

    /**
     * Scope to get the tracks that are currently on playlists
     * ie appear on a snapshot that is the latest for a playlist.
     */
    public function scopeOnCurrentPlaylist($query)
    {
        return $query->whereHas('playlistSnapshots', function ($query) {
            return $query->current();
        });
    }

    /**
     * Get the playlistSnapshots for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function playlistSnapshots()
    {
        return $this->belongsToMany(
            config('music-services.models.playlist_snapshot', PlaylistSnapshot::class),
            config('music-services.table_names.playlist_snapshot_track_pivot'),
            'track_id',
            'playlist_snapshot_id'
        )->using(PlaylistTrackPivot::class)
            ->withPivot('order', 'added_at', 'meta');
    }

    /**
     * Get the playlistSnapshots for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function currentSnapshots()
    {
        return $this->playlistSnapshots()->current();
    }

    /**
     * Get the album for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function album()
    {
        return $this->belongsTo(
            config('music-services.models.album', Album::class),
            'album_id'
        );
    }

    /**
     * Get the trackInformation for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trackInformation()
    {
        return $this->hasMany(
            config('music-services.models.track_information', TrackInformation::class),
            'track_id'
        );
    }

    /**
     * Get the trackInformation for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function audioFeatures()
    {
        return $this->hasOne(config('music-services.models.track_information', TrackInformation::class))
            ->audioFeatures();
    }
}
