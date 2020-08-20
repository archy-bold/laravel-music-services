<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Contracts;

use ArchyBold\LaravelMusicServices\Repositories\Contracts\Repository;

interface PlaylistRepository extends Repository
{
    // Custom repository

    /**
     * Get a playlist from an external vendor, store it and return the object.
     *
     * @param string $id
     * @return \App\Playlist
     */
    public function get($id);

    /**
     * Get a CSV representation of a playlist.
     *
     * @param string|int|\App\Playlist $playlist
     * @param array $headers
     * @param array $columns
     * @param array $load = []
     * @return array
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getCsv($playlist, $headers, $columns, $load = []);

    /**
     * As the `get` function, will retrieve the playlist, store and will then
     * take a snapshot of the current playlist state.
     *
     * @param string $id
     * @return \App\PlaylistSnapshot
     */
    public function createSnapshot($id);

    /**
     * Get the playlists for a user from an external vendor, store them and return the objects.
     *
     * @param string $userId
     * @return \App\Playlist[]
     */
    public function getAllForUser($userId);
}
