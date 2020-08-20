<?php

namespace ArchyBold\LaravelMusicServices\Services\Contracts;

/**
 * Interface for services interacting with external DSP vendors.
 */
interface VendorService
{
    /**
     * Authorise against the external service.
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function authenticate();

    /**
     * Retrieve a track from the external service.
     *
     * @param string $id
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getTrack($id);

    /**
     * Retrieve a track's audio features from the external service.
     *
     * @param string $id
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getTrackAudioFeatures($id);

    /**
     * Retrieve a playlist from the external service.
     *
     * @param string $id
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getPlaylist($id);

    /**
     * Retrieve a playlist's tracks from the external service, may be paginated.
     *
     * @param string $id
     * @param integer $page = 1
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getPlaylistTracks($id, $page = 1);

    /**
     * Retrieve all the playlist's tracks from the external service, unpaginated.
     *
     * @param string $id
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getAllPlaylistTracks($id);

    /**
     * Retrieve a users's playlists from the external service, may be paginated.
     *
     * @param string $id
     * @param integer $page = 1
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getUserPlaylists($id, $page = 1);

    /**
     * Retrieve all the users's playlists from the external service, may be paginated.
     *
     * @param string $id
     * @param integer $page = 1
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getAllUserPlaylists($id);

    /**
     * Parse the given ID, eg extract ID from url
     *
     * @param string $id
     * @param string $type
     * @return string
     */
    public function parseId($id, $type);
}
