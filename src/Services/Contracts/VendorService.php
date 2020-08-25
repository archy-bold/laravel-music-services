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
     * Set the token on the service, avoiding the authenticate function.
     *
     * @param string $token
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function setAccessToken($token);

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
     * Create a playlist for the logged in user on the external service.
     *
     * @param array $attrs
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createPlaylist($attrs);

    /**
     * Add tracks to a playlist for the logged in user on the external service.
     *
     * @param string $id
     * @param array $tracks An array of the track IDs to add
     * @param int $position = null Zero-based position of the tracks, null appends to the end.
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addPlaylistTracks($id, $tracks, $position = null);

    /**
     * Parse the given ID, eg extract ID from url
     *
     * @param string $id
     * @param string $type
     * @return string
     */
    public function parseId($id, $type);
}
