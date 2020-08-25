<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Contracts;

use ArchyBold\LaravelMusicServices\Repositories\Contracts\Repository;
use Illuminate\Support\Collection;

interface PlaylistRepository extends Repository
{
    // Custom repository

    /**
     * Get a playlist from an external vendor, store it and return the object.
     *
     * @param string $id
     * @return \ArchyBold\LaravelMusicServices\Playlist
     */
    public function get($id);

    /**
     * Get a CSV representation of a playlist.
     *
     * @param string|int|\ArchyBold\LaravelMusicServices\Playlist $playlist
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
     * @return \ArchyBold\LaravelMusicServices\PlaylistSnapshot
     */
    public function createSnapshot($id);

    /**
     * Get the playlists for a user from an external vendor, store them and return the objects.
     *
     * @param string $userId
     * @return \ArchyBold\LaravelMusicServices\Playlist[]
     */
    public function getAllForUser($userId);

    /**
     * Create a playlist for the logged-in user for an external vendor,
     * store ir and return the Playlist.
     *
     * @param array $attrs
     * @return \ArchyBold\LaravelMusicServices\Playlist
     */
    public function create($attrs);

    /**
     * Add tracks to the playlist from an external vendor
     * store ir and return the Playlist.
     *
     * @param string $id The playlist ID
     * @param \Illuminate\Support\Collection $tracks The tracks to add to the playlist
     * @param int $position = null The zero-based position at which to add the tracks
     * @return boolean
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function addTracks($id, Collection $tracks, $position = null);
}
