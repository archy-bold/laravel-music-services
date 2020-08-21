<?php

namespace ArchyBold\LaravelMusicServices;

use ArchyBold\LaravelMusicServices\Pivots\PlaylistTrackPivot;
use DB;
use Illuminate\Database\Eloquent\Model;

class PlaylistSnapshot extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'num_followers',
        'meta',
        'playlist_id',
        'created_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'num_followers' => 'integer',
        'meta' => 'array',
        'playlist_id' => 'integer',
    ];

    public function getTable()
    {
        return config('music-services.table_names.playlist_snapshots', parent::getTable());
    }

    /**
     * Scope to get the current snapshots.
     */
    public function scopeCurrent($query)
    {
        $playlistClass = config('music-services.models.playlist', Playlist::class);
        $snapshotClass = config('music-services.models.playlist_snapshot', PlaylistSnapshot::class);
        // Get the IDs of all latest snapshots.
        return $query->whereIn('id', function ($query) use ($playlistClass, $snapshotClass) {
            return $query->from(with(new $playlistClass)->getTable() . ' AS p')
                ->selectSub(function ($query) use ($snapshotClass) {
                    $query->select('id')
                        ->from(with(new $snapshotClass)->getTable())
                        ->where('playlist_id', DB::raw('p.id'))
                        ->latest()
                        ->limit(1);
                }, 'plalist_snapshot_id');
        });
    }

    /**
     * Get the playlist for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function playlist()
    {
        return $this->belongsTo(
            config('music-services.models.playlist', Playlist::class),
            'playlist_id'
        );
    }

    /**
     * Get the tracks for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tracks()
    {
        return $this->belongsToMany(
            config('music-services.models.track', Track::class),
            config('music-services.table_names.playlist_snapshot_track_pivot'),
            'playlist_snapshot_id',
            'track_id'
        )->using(PlaylistTrackPivot::class)
            ->withPivot('order', 'added_at', 'meta')
            ->orderBy('order');
    }
}
