<?php

namespace ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent;

use ArchyBold\LaravelMusicServices\PlaylistSnapshot;
use ArchyBold\LaravelMusicServices\Repositories\Repository;
use ArchyBold\LaravelMusicServices\Track;
use ArchyBold\LaravelMusicServices\Playlist;
use ArchyBold\LaravelMusicServices\Album;
use ArchyBold\LaravelMusicServices\User;
use ArchyBold\LaravelMusicServices\Services\Contracts\VendorService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

abstract class PlaylistRepository extends Repository
{
    /**
     * The entity for this repository.
     *
     * @var string $entity
     */
    protected $entity = Playlist::class;

    /** @var VendorService */
    protected $service;
    /** @var boolean */
    protected $authenticated = false;

    /**
     * @param VendorService $service
     */
    public function __construct(VendorService $service)
    {
        $this->service = $service;
    }

    protected function authenticate()
    {
        if (!$this->authenticated) {
            $this->service->authenticate();
            $this->authenticated = true;
        }
    }

    /**
     * Get a playlist from an external vendor, store it and return the object.
     *
     * @param string $id
     * @return \ArchyBold\LaravelMusicServices\Playlist
     */
    public function get($id)
    {
        $this->authenticate();
        $id = $this->service->parseId($id, 'playlist');

        // Get the playlist and map to attributes
        $playlist = $this->service->getPlaylist($id);
        return $this->createModels($playlist, $id);
    }

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
    public function getCsv($playlist, $headers, $columns, $load = [])
    {
        if (is_numeric($playlist)) {
            $playlist = Playlist::find($playlist);
        }
        else if (is_string($playlist)) {
            $playlist = Playlist::vendorFind($this->getVendor(), $playlist)->first();
        }

        if (!$playlist instanceof Playlist) {
            throw new ModelNotFoundException(__('laravel-music-services::error.not-found'));
        }

        $load[] = 'owner';
        $playlist->load($load);

        $csv = [
            ['Playlist', $playlist->name],
            ['User', $playlist->owner->vendor_id],
            ['URI', $playlist->uri],
            ['URL', $playlist->url],
            [...$headers],
        ];

        if ($playlist->latestSnapshot) {
            $tracks = $playlist->latestSnapshot->tracks->map(function ($track) use ($columns) {
                $dot = Arr::dot($track->toArray());
                return collect($columns)->map(function ($column) use ($dot) {
                    return $dot[$column] ?? '';
                });
            })->toArray();
            $csv = array_merge($csv, $tracks);
        }
        return $csv;
    }

    /**
     * As the `get` function, will retrieve the playlist, store and will then
     * take a snapshot of the current playlist state.
     *
     * @param string $id
     * @return \ArchyBold\LaravelMusicServices\Services\PlaylistSnapshot
     */
    public function createSnapshot($id)
    {
        $id = $this->service->parseId($id, 'playlist');

        // Start by getting the playlist
        $playlist = $this->get($id);

        if (is_null($playlist)) {
            return null;
        }

        // Then get the playlist tracks
        $Tracks = $this->service->getAllPlaylistTracks($id);

        if (is_null($Tracks)) {
            return null;
        }

        // If we've .got the tracks, create the snapshot.
        $servicePlaylist = $this->service->getPlaylist($id);
        $snapshotAttrs = $this->mapServicePlaylistToSnapshotAttributes($servicePlaylist);
        $snapshotAttrs['playlist_id'] = $playlist->id;
        $snapshot = PlaylistSnapshot::create($snapshotAttrs);

        // Then get the track attributes
        $tracksAttrs = $this->mapServicePlaylistTracksToAttributes($Tracks);

        // For each track, create the album, get the track and join to the snapshot
        foreach ($tracksAttrs as $i => $trackAttrs) {
            $albumAttrs = $trackAttrs['album'] ?? null;
            if ($albumAttrs) {
                $album = $this->getAlbum($albumAttrs);
                $trackAttrs = collect($trackAttrs)->except('album')->toArray();
                $trackAttrs['album_id'] = $album->id;
            }

            $track = $this->getTrack($trackAttrs);
            $pivot = array_merge(['order' => $i], $trackAttrs['pivot']);
            $snapshot->tracks()->attach($track->id, $pivot);
        }

        return $snapshot;
    }

    /**
     * Get the playlists for a user from an external vendor, store them and return the objects.
     *
     * @param string $userId
     * @return \Illuminate\Support\Collection
     */
    public function getAllForUser($userId)
    {
        $this->authenticate();
        $userId = $this->service->parseId($userId, 'user');

        // Get all the playlists and map to attributes
        $playlists = $this->service->getAllUserPlaylists($userId);
        $playlists = $this->mapServiceUserPlaylistsToArray($playlists);
        $models = collect();
        foreach ($playlists as $playlist) {
            $playlist = $this->createModels($playlist);
            if (!is_null($playlist)) {
                $models->push($playlist);
            }
        }
        return $models;
    }

    /**
     * Get the vendor string eg 'spotify'
     *
     * @return string
     */
    abstract public function getVendor();

    /**
     * Maps the service playlist to a Playlist attributes array.
     *
     * @param array $playlist
     * @return array
     */
    abstract protected function mapServicePlaylistToAttributes($playlist);

    /**
     * Maps the service playlist to a PlaylistSnapshot attributes array.
     *
     * @param array $playlist
     * @return array
     */
    abstract protected function mapServicePlaylistToSnapshotAttributes($playlist);

    /**
     * Maps the user service playlists to arrays.
     *
     * @param array $playlists
     * @return array
     */
    abstract protected function mapServiceUserPlaylistsToArray($playlists);

    /**
     * Maps the service playlist to a VendorUser attributes array.
     *
     * @param array $playlist
     * @return array
     */
    abstract protected function mapServicePlaylistToUserAttributes($playlist);

    /**
     * Maps the service playlist tracks to Vendor Track attributes arrays.
     *
     * @param array $playlist
     * @return array
     */
    abstract protected function mapServicePlaylistTracksToAttributes($Tracks);

    /**
     * Get the ISRC from a vendor track.
     *
     * @param array $Track
     * @return array
     */
    abstract protected function getIsrcFromTrack($Track);

    /**
     * Function to generate the models for a service playlist.
     *
     * @param array $playlist
     * @return ArchyBold\LaravelMusicServices\Playlist|null
     */
    protected function createModels($playlist, $id = null)
    {
        if (!is_null($playlist) && !empty($playlist)) {
            // First create the user, if required
            $userAttrs = $this->mapServicePlaylistToUserAttributes($playlist);
            $user = User::vendorFind($this->getVendor(), $userAttrs['vendor_id'])->first();

            if (is_null($user)) {
                $user = User::create($userAttrs);
            }
            else {
                $user->update($userAttrs);
            }

            // Map the attributes, setting the user.
            $playlistAttrs = $this->mapServicePlaylistToAttributes($playlist);
            $playlistAttrs['owner_id'] = $user->id;

            // Next check if the playlist already exists.
            if (!$id) {
                $id = $playlistAttrs['vendor_id'];
            }
            $playlist = Playlist::vendorFind($this->getVendor(), $id)->first();

            // Now either update the existing playlist or create a new one.
            if (is_null($playlist)) {
                $playlist = Playlist::create($playlistAttrs);
            }
            else {
                $playlist->update($playlistAttrs);
            }

            return $playlist->fresh();
        }
        return null;
    }

    /**
     * Gets the vendor track for the given attributes. If it doesn't exist, it creates;
     * if it does, it updates. Will then check for matching Tracks and join them.
     *
     * @param array $attrs
     * @return Track
     */
    protected function getTrack($attrs)
    {
        $track = Track::vendorFind($this->getVendor(), $attrs['vendor_id'])->first();

        // Now either update the existing playlist or create a new one.
        if (is_null($track)) {
            $track = Track::create($attrs);
        }
        else {
            $track->update($attrs);
        }

        // If there's no ISRC, we need to hit the track API to get it.
        if (!$track->isrc) {
            $apiTrack = $this->service->getTrack($attrs['vendor_id']);
            if ($apiTrack) {
                $track->isrc = $this->getIsrcFromTrack($apiTrack);
            }
        }
        $attrs['isrc'] = $track->isrc;

        // TODO Find the existing matching track
        // if ($track = $this->findMatchingTrack($attrs)) {
        //     $track->track_id = $track->id;
        // }
        $track->save();

        return $track;
    }

    /**
     * Gets the album for the given attributes. If it doesn't exist, it creates;
     * if it does, it updates.
     *
     * @param array $attrs
     * @return Album
     */
    protected function getAlbum($attrs)
    {
        $album = Album::vendorFind($this->getVendor(), $attrs['vendor_id'])->first();

        // Now either update the existing playlist or create a new one.
        if (is_null($album)) {
            $album = Album::create($attrs);
        }
        else {
            $album->update($attrs);
        }

        // TODO Do we need to hit the API for the UPC?

        // TODO Find the existing matching album
        // if ($album = $this->findMatchingAlbum($attrs)) {
        //     $album->album_id = $album->id;
        // }
        $album->save();

        return $album;
    }

    /**
     * Find the matching track for the given Track.
     *
     * @param array $TrackAttrs
     * @return Track
     */
    public function findMatchingTrack($TrackAttrs)
    {
        // Find all tracks with the same ISRC.
        $matches = Track::with('albums')->whereIsrc($TrackAttrs['isrc'])->get();
        $count = $matches->count();
        if ($count == 1) {
            // If there's one match, assume it's the same track.
            return $matches->first();
        }
        else if ($count > 1) {
            // If there are multiple matches, find the one with the same album.
            foreach ($matches as $match) {
                foreach ($match->albums as $album) {
                    if ($album->title == $TrackAttrs['album']) {
                        return $match;
                    }
                }
            }
        }
        return null;
    }
}
