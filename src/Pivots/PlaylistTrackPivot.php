<?php

namespace ArchyBold\LaravelMusicServices\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PlaylistTrackPivot extends Pivot
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'order' => 'integer',
        'meta' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
	 */
    protected $dates = [
        'added_at',
    ];
}
