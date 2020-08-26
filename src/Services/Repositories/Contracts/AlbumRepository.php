<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Contracts;

interface AlbumRepository
{
    // Custom repository

    /**
     * Get an album from an external vendor, store it and return the object.
     *
     * @param string $id
     * @return \ArchyBold\LaravelMusicServices\Album
     */
    public function get($id);
}
