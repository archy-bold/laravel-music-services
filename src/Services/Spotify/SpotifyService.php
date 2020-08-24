<?php

namespace ArchyBold\LaravelMusicServices\Services\Spotify;

use ArchyBold\LaravelMusicServices\Services\ApiCall;
use ArchyBold\LaravelMusicServices\Services\Contracts\CachesApi;
use ArchyBold\LaravelMusicServices\Services\Contracts\VendorService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SpotifyService implements VendorService
{
    use CachesApi;

    const TRACK_PAGE_SIZE = 100;
    const PLAYLIST_PAGE_SIZE = 50;

    /** @var Session */
    protected $session;
    /** @var string */
    protected $accessToken;
    /** @var SpotifyWebAPI */
    protected $api;

    /**
     * @param Session $session
     */
    public function __construct(Session $session, SpotifyWebAPI $api = null)
    {
        $this->session = $session;
        $this->api = $api;
    }

    /**
     * Authorise against the external service.
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function authenticate()
    {
        try {
            $this->session->requestCredentialsToken();
            $this->accessToken = $this->session->getAccessToken();
        }
        catch (\Exception $e) {
            throw new AuthenticationException(
                __('laravel-music-services::error.service.authentication', [
                    'service' => 'Spotify',
                    'message' => $e->getMessage(),
                ])
            );
        }

        $this->api = new SpotifyWebAPI();
        $this->api->setAccessToken($this->accessToken);
        $this->api->setReturnType(SpotifyWebAPI::RETURN_ASSOC);
    }

    /**
     * Set the token on the service, avoiding the authenticate function.
     *
     * @param string $token
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
        $this->api = new SpotifyWebAPI();
        $this->api->setAccessToken($this->accessToken);
        $this->api->setReturnType(SpotifyWebAPI::RETURN_ASSOC);
    }

    /**
     * Retrieve a track from the external service.
     *
     * @param string $id
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getTrack($id)
    {
        $call = new ApiCall('getTrack', $id);
        return $this->doApiCall($call);
    }

    /**
     * Retrieve a track's audio features from the external service.
     *
     * @param string $id
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getTrackAudioFeatures($id)
    {
        $call = new ApiCall('getAudioFeatures', $id);
        $result = $this->doApiCall($call);
        return $result['audio_features'][0] ?? $result;
    }

    /**
     * Retrieve a playlist from the external service.
     *
     * @param string $id
     * @return array
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getPlaylist($id)
    {
        $call = new ApiCall('getPlaylist', $id);
        return $this->doApiCall($call);
    }

    /**
     * Retrieve a playlist's tracks from the external service, may be paginated.
     *
     * @param string $id
     * @param integer $page = 1
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getPlaylistTracks($id, $page = 1)
    {
        $call = new ApiCall('getPlaylistTracks', $id, [
            'limit' => self::TRACK_PAGE_SIZE,
            'offset' => self::TRACK_PAGE_SIZE * ($page - 1),
        ]);

        // If there's a getPlaylist call for this ID, and this is a request for page 1,
        // We can return the cached playlist.
        $getPlaylistCall = new ApiCall('getPlaylist', $id);
        if ($page == 1 && ($result = $this->retrieveFromCache($getPlaylistCall))) {
            return $result['tracks'];
        }

        return $this->doApiCall($call);
    }

    /**
     * Retrieve a playlist's tracks from the external service, unpaginated.
     *
     * @param string $id
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getAllPlaylistTracks($id)
    {
        $result = null;

        // Loop through the playlist tracks pages
        $page = 1;
        while ($tracks = $this->getPlaylistTracks($id, $page)) {
            if (is_null($result)) {
                $result = $tracks;
            }
            else {
                $result['items'] = array_merge($result['items'], $tracks['items']);
            }

            if (is_null($tracks['next'])) {
                break;
            }

            $page++;
        }

        return $result;
    }

    /**
     * Retrieve a users's playlists from the external service, may be paginated.
     *
     * @param string $id
     * @param integer $page = 1
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getUserPlaylists($id, $page = 1)
    {
        $call = new ApiCall('getUserPlaylists', $id, [
            'limit' => self::PLAYLIST_PAGE_SIZE,
            'offset' => self::PLAYLIST_PAGE_SIZE * ($page - 1),
        ]);

        return $this->doApiCall($call);
    }

    /**
     * Retrieve all the users's playlists from the external service, may be paginated.
     *
     * @param string $id
     * @param integer $page = 1
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getAllUserPlaylists($id)
    {
        $result = null;

        // Loop through the user playlists pages
        $page = 1;
        while ($playlists = $this->getUserPlaylists($id, $page)) {
            if (is_null($result)) {
                $result = $playlists;
            }
            else {
                $result['items'] = array_merge($result['items'], $playlists['items']);
            }

            if (is_null($playlists['next'])) {
                break;
            }

            $page++;
        }

        return $result;
    }

    /**
     * Create a playlist for the logged in user on the external service.
     *
     * @param array $attrs
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createPlaylist($attrs)
    {
        $call = new ApiCall('createPlaylist', '', $attrs);
        $call->setCacheable(false);
        $call->setRequiresId(false);
        return $this->doApiCall($call);
    }

    /**
     * Parse the given ID, eg extract ID from url
     *
     * @param string $id
     * @param string $type
     * @return string
     */
    public function parseId($id, $type)
    {
        $typeLength = strlen($type);
        if (filter_var($id, FILTER_VALIDATE_URL)) {
            $url = parse_url($id);
            if (strpos($url['path'], '/' . $type . '/') === 0) {
                $id = substr($url['path'], $typeLength + 2);
            }
        }
        if (strpos($id, 'spotify:' . $type . ':') === 0) {
            $id = substr($id, 9 + $typeLength);
        }
        return $id;
    }

    /**
     * Do the API call, handle exceptions, ID parsing and cache.
     *
     * @param ApiCall $call
     * @return array
     */
    protected function doApiCall(ApiCall $call)
    {
        if (is_null($this->api)) {
            throw new AuthenticationException(__('laravel-music-services::auth.unauthenticated'));
        }

        // Get everything from the call.
        $function = $call->getFunction();
        $id = $call->getId();
        $options = $call->getOptions();

        // Ensure the ID is as expected
        if ($call->requiresId()) {
            $type = null;
            if (strpos($function, 'getPlaylist') === 0) {
                $type = 'playlist';
            }
            else if (strpos($function, 'getTrack') === 0 || strpos($function, 'getAudioFeatures') === 0) {
                $type = 'track';
            }
            else if (strpos($function, 'getUserPlaylists') === 0) {
                $type = 'user';
            }
            $id = $this->parseId($id, $type);
        }

        $result = null;

        // Check if it's stored in the cache.
        if ($call->isCacheable() && $result = $this->retrieveFromCache($call)) {
            return $result;
        }

        try {
            if ($call->requiresId()) {
                $result = $this->api->$function($id, $options);
            }
            else {
                $result = $this->api->$function($options);
            }
            // Add the result to the cache.
            if ($call->isCacheable()) {
                $this->addToCache($call, $result);
            }
        }
        catch (SpotifyWebAPIException $e) {
            if ($e->getCode() == 429) {
                // If we're rate limited, sleep then call again.
                $lastResponse = $this->api->getRequest()->getLastResponse();
                $retryAfter = $lastResponse['headers']['retry-after'];
                sleep($retryAfter);
                return $this->doApiCall($call);
            }
            else if ($e->getCode() == 401) {
                throw new AuthorizationException(
                    __('laravel-music-services::error.service.unauthorised', ['message' => $e->getMessage()])
                );
            }
            else if ($e->getCode() == 400) {
                throw new BadRequestHttpException(
                    __('laravel-music-services::error.service.invalid-request', ['message' => $e->getMessage()])
                );
            }
            else {
                // dd($e->getCode());
                throw new NotFoundHttpException(
                    __('laravel-music-services::error.service.not-found', ['message' => $e->getMessage()])
                );
            }
        }
        return $result;
    }
}
