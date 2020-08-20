<?php

namespace ArchyBold\LaravelMusicServices\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelMusicServices extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-music-services';
    }
}
