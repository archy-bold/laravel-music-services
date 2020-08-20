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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('music-services.tables.playlist_snapshots'));
    }

    /**
     * Scope to get the current snapshots.
     */
    public function scopeCurrent($query)
    {
        // Get the IDs of all latest snapshots.
        return $query->whereIn('id', function ($query) {
            return $query->from(with(new VendorPlaylist)->getTable() . ' AS p')
                ->selectSub(function ($query) {
                    $query->select('id')
                        ->from(with(new PlaylistSnapshot)->getTable())
                        ->where('playlist_id', DB::raw('p.id'))
                        ->latest()
                        ->limit(1);
                }, 'plalist_snapshot_id');
        });
    }

    /**
     * Get the vendorPlaylist for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendorPlaylist()
    {
        return $this->belongsTo(VendorPlaylist::class);
    }

    /**
     * Get the tracks for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tracks()
    {
        return $this->belongsToMany(
            VendorTrack::class,
            'playlist_snapshot_vendor_track',
            'playlist_snapshot_id',
            'vendor_track_id'
        )->using(PlaylistTrackPivot::class)
            ->withPivot('order', 'added_at', 'meta')
            ->orderBy('order');
    }
}
