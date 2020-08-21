<?php

namespace ArchyBold\LaravelMusicServices;

use ArchyBold\LaravelMusicServices\Traits\VendorModel;
use Illuminate\Database\Eloquent\Model;

class TrackInformation extends Model
{
    use VendorModel;

    /** @var string */
    public const AUDIO_FEATURES = 'audio_features';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'vendor',
        'meta',
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
        'camelot_code',
        'duration_s',
    ];

    public function getTable()
    {
        return config('music-services.table_names.track_information', parent::getTable());
    }

    /**
     * Scope to get the current snapshots.
     */
    public function scopeAudioFeatures($query, $vendor = null)
    {
        $query = $query->whereType(self::AUDIO_FEATURES);
        if ($vendor) {
            $query = $query->whereVendor($vendor);
        }
        return $query;
    }

    /**
     * Get the track for this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function track()
    {
        return $this->belongsTo(
            config('music-services.models.track', Track::class),
            'track_id'
        );
    }

    /**
     * Get the duration in seconds.
     *
     * @return string|null
     */
    public function getDurationSAttribute()
    {
        return isset($this->meta['duration_ms']) && is_numeric($this->meta['duration_ms'])
            ? $this->meta['duration_ms'] / 1000
            : null;
    }

    /**
     * Get the camelot code based on the audio information stored.
     *
     * @return string|null
     */
    public function getCamelotCodeAttribute()
    {
        // Only works for Spotify audio features right now
        if ($this->type == self::AUDIO_FEATURES && $this->vendor == 'spotify') {
            $key = $this->meta['key'] ?? null;
            $mode = $this->meta['mode'] ?? null;

            // key
            // 0 C
            // 1 C♯, D♭
            // 2 D
            // 3 D♯, E♭
            // 4 E
            // 5 F
            // 6 F♯, G♭
            // 7 G
            // 8 G♯, A♭
            // 9 A
            // 10 A♯, B♭
            // 11 B

            // mode
            // 0 minor
            // 1 major

            // Minor key
            if ($mode === 0) {
                switch ($key) {
                    case 0:
                        return '5A';
                    case 1:
                        return '12A';
                    case 2:
                        return '7A';
                    case 3:
                        return '2A';
                    case 4:
                        return '9A';
                    case 5:
                        return '4A';
                    case 6:
                        return '11A';
                    case 7:
                        return '6A';
                    case 8:
                        return '1A';
                    case 9:
                        return '8A';
                    case 10:
                        return '3A';
                    case 11:
                        return '10A';
                }
            }
            // Major key
            else if ($mode === 1) {
                switch ($key) {
                    case 0:
                        return '8B';
                    case 1:
                        return '3B';
                    case 2:
                        return '10B';
                    case 3:
                        return '5B';
                    case 4:
                        return '12B';
                    case 5:
                        return '7B';
                    case 6:
                        return '2B';
                    case 7:
                        return '9B';
                    case 8:
                        return '4B';
                    case 9:
                        return '11B';
                    case 10:
                        return '6B';
                    case 11:
                        return '1B';
                }
            }
        }
        return null;
    }
}
