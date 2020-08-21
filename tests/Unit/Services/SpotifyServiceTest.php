<?php

namespace ArchyBold\LaravelMusicServices\Tests\Unit\Services;

use ArchyBold\LaravelMusicServices\Services\Spotify\SpotifyService;
use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsSpotifyApi;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use phpmock\phpunit\PHPMock;
use SpotifyWebAPI\Request;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;

class SpotifyServiceTest extends TestCase
{
    use TestsSpotifyApi, PHPMock;

    /**
     * Test getting the Service from the AppServiceProvider.
     *
     * @return void
     */
    public function test_makeService()
    {
        config([
            'music-services.spotify.client_id' => 'client_id',
            'music-services.spotify.client_secret' => 'client_secret',
        ]);

        $service = $this->app->make(SpotifyService::class);

        $this->assertInstanceOf(SpotifyService::class, $service);
        // Check the session is set with the correct information.
        $session = $this->getProperty($service, 'session');
        $this->assertEquals('client_id', $session->getClientId());
        $this->assertEquals('client_secret', $session->getClientSecret());
    }

    /**
     * Test authenticate - succeeds.
     *
     * @return void
     */
    public function test_authenticate()
    {
        // Set up the mock session.
        $session = $this->createMock(Session::class);
        $session->expects($this->once())
            ->method('requestCredentialsToken');
        $session->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('access_token');

        $service = new SpotifyService($session);
        $service->authenticate();

        // Make the assertions.
        $this->assertEquals('access_token', $this->getProperty($service, 'accessToken'));
        $this->assertNotEquals(null, $this->getProperty($service, 'api'));
        $api = $this->getProperty($service, 'api');
        $this->assertEquals('access_token', $this->getProperty($api, 'accessToken'));
        $request = $this->getProperty($api, 'request');
        $this->assertEquals(SpotifyWebAPI::RETURN_ASSOC, $request->getReturnType());
    }

    /**
     * Test authenticate - fails.
     *
     * @return void
     * @dataProvider authenticateFailureProvider
     */
    public function test_authenticate_failure($throws, $message)
    {
        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $this->expectExceptionMessage($message);

        // Set up the mock session.
        $session = $this->createMock(Session::class);
        $session->expects($this->once())
            ->method('requestCredentialsToken')
            ->will($this->throwException($throws));
        $session->expects($this->never())
            ->method('getAccessToken')
            ->will($this->throwException($throws));

        // Authenticate
        $service = new SpotifyService($session);
        try {
            $service->authenticate();
        }
        finally {
            $this->assertEquals(null, $this->getProperty($service, 'accessToken'));
            $this->assertEquals(null, $this->getProperty($service, 'api'));
        }
    }

    public function authenticateFailureProvider()
    {
        return [
            'random error' => [
                new \Exception('Could not connect to Spotify.'),
                'Cannot authenticate Spotify: Could not connect to Spotify.',
            ],
            'empty credentials' => [
                new SpotifyWebAPIException('An unknown error occurred.', 400),
                'Cannot authenticate Spotify: An unknown error occurred.',
            ],
            'invalid client ID' => [
                new SpotifyWebAPIException('Invalid client', 400),
                'Cannot authenticate Spotify: Invalid client',
            ],
            'invalid secret' => [
                new SpotifyWebAPIException('Invalid client secret', 400),
                'Cannot authenticate Spotify: Invalid client secret',
            ],
        ];
    }

    /**
     * Test getTrack
     *
     * @return void
     * @dataProvider getTrackProvider
     */
    public function test_getTrack($id, $expectedId)
    {
        $expectedTrack = ['id' => '3135556', 'title' => 'Harder, Better, Faster, Stronger'];

        // Set up the mocks.
        $session = $this->createMock(Session::class);
        $api = $this->createMock(SpotifyWebAPI::class);
        $api->expects($this->once())
            ->method('getTrack')
            ->with($this->equalTo($expectedId))
            ->will($this->returnValue($expectedTrack));

        $service = new SpotifyService($session, $api);

        // Get the track
        $track = $service->getTrack($id);

        $this->assertEquals($expectedTrack, $track);

        // Perform a second request to ensure the API is only hit once.
        $track = $service->getTrack($id);
        $this->assertEquals($expectedTrack, $track);
    }

    public function getTrackProvider()
    {
        return $this->getParseIdTests('track');
    }

    /**
     * Test getTrack - fails.
     *
     * @return void
     * @dataProvider apiFailureProvider
     */
    public function test_getTrack_failure($id, $throws, $exceptionClass, $message, $setApi = true)
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($message);

        // Set up the mocks.
        $session = $this->createMock(Session::class);
        $service = null;
        if ($setApi) {
            $api = $this->createMock(SpotifyWebAPI::class);
            $api->expects($this->once())
                ->method('getTrack')
                ->with($this->equalTo($id))
                ->will($this->throwException($throws));

            $service = new SpotifyService($session, $api);
        }
        else {
            $service = new SpotifyService($session);
        }

        $service->getTrack($id);
    }

    /**
     * Test getTrack - retries when rate limited.
     *
     * @return void
     */
    public function test_getTrack_rateLimited()
    {
        $id = 'abc123';
        $expectedTrack = ['id' => '3135556', 'title' => 'Harder, Better, Faster, Stronger'];

        // Set up the sleep mock
        $reflect = new \ReflectionClass(SpotifyService::class);
        $namespace = $reflect->getNamespaceName();

        // Set up the mocks.
        $retryAfter = 10;
        $sleep = $this->getFunctionMock($namespace, 'sleep');
        $sleep->expects($this->once())
            ->with($this->equalTo($retryAfter));
        $response = ['headers' => ['retry-after' => $retryAfter]];
        $request = $this->createMock(Request::class);
        $request->expects($this->exactly(1))
            ->method('getLastResponse')
            ->will($this->returnValue($response));
        $exception = new SpotifyWebAPIException('An unknown error occurred.', 429);
        $session = $this->createMock(Session::class);
        $api = $this->createMock(SpotifyWebAPI::class);
        $api->expects($this->exactly(2))
            ->method('getTrack')
            ->with($this->equalTo($id))
            ->will($this->onConsecutiveCalls(
                $this->throwException($exception),
                $this->returnValue($expectedTrack)
            ));
        $api->expects($this->once(2))
            ->method('getRequest')
            ->will($this->returnValue($request));

        $service = new SpotifyService($session, $api);

        // Get the track
        $track = $service->getTrack($id);

        $this->assertEquals($expectedTrack, $track);
    }

    /**
     * Test getTrackAudioFeatures
     *
     * @return void
     * @dataProvider getTrackAudioFeaturesProvider
     */
    public function test_getTrackAudioFeatures($id, $expectedId)
    {
        // Set up the mocks.
        $session = $this->createMock(Session::class);
        $api = $this->createMock(SpotifyWebAPI::class);
        $api->expects($this->once())
            ->method('getAudioFeatures')
            ->with($this->equalTo($expectedId))
            ->will($this->returnValue($this->getSpotifyAudioFeatures()));

        $service = new SpotifyService($session, $api);

        // Get the playlist
        $features = $service->getTrackAudioFeatures($id);

        $this->assertEquals(0.691, $features['danceability']);
        $this->assertEquals(0.762, $features['energy']);
        $this->assertEquals(11, $features['key']);

        // Perform a second request to ensure the API is only hit once.
        $features = $service->getTrackAudioFeatures($id);
        $this->assertEquals(0.691, $features['danceability']);
        $this->assertEquals(0.762, $features['energy']);
        $this->assertEquals(11, $features['key']);
    }

    public function getTrackAudioFeaturesProvider()
    {
        return $this->getParseIdTests('track');
    }

    /**
     * Test getTrackAudioFeatures - fails.
     *
     * @return void
     * @dataProvider apiFailureProvider
     */
    public function test_getTrackAudioFeatures_failure($id, $throws, $exceptionClass, $message, $setApi = true)
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($message);

        // Set up the mocks.
        $session = $this->createMock(Session::class);
        $service = null;
        if ($setApi) {
            $api = $this->createMock(SpotifyWebAPI::class);
            $api->expects($this->once())
                ->method('getAudioFeatures')
                ->with($this->equalTo($id))
                ->will($this->throwException($throws));

            $service = new SpotifyService($session, $api);
        }
        else {
            $service = new SpotifyService($session);
        }

        $service->getTrackAudioFeatures($id);
    }

    /**
     * Test getPlaylist
     *
     * @return void
     * @dataProvider getPlaylistProvider
     */
    public function test_getPlaylist($id, $expectedId)
    {
        $expectedPlaylist = ['name' => 'Ridonculous'];

        // Set up the mocks.
        $session = $this->createMock(Session::class);
        $api = $this->createMock(SpotifyWebAPI::class);
        $api->expects($this->once())
            ->method('getPlaylist')
            ->with($this->equalTo($expectedId))
            ->will($this->returnValue($expectedPlaylist));

        $service = new SpotifyService($session, $api);

        // Get the playlist
        $playlist = $service->getPlaylist($id);

        $this->assertEquals($expectedPlaylist, $playlist);

        // Perform a second request to ensure the API is only hit once.
        $tracks = $service->getPlaylist($id);
        $this->assertEquals($expectedPlaylist, $playlist);
    }

    public function getPlaylistProvider()
    {
        return $this->getParseIdTests('playlist');
    }

    /**
     * Test getPlaylist - fails.
     *
     * @return void
     * @dataProvider apiFailureProvider
     */
    public function test_getPlaylist_failure($id, $throws, $exceptionClass, $message, $setApi = true)
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($message);

        // Set up the mocks.
        $session = $this->createMock(Session::class);
        $service = null;
        if ($setApi) {
            $api = $this->createMock(SpotifyWebAPI::class);
            $api->expects($this->once())
                ->method('getPlaylist')
                ->with($this->equalTo($id))
                ->will($this->throwException($throws));

            $service = new SpotifyService($session, $api);
        }
        else {
            $service = new SpotifyService($session);
        }

        $service->getPlaylist($id);
    }

    public function apiFailureProvider()
    {
        return [
            'empty id' => [
                '',
                new SpotifyWebAPIException('An unknown error occurred.', 404),
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                'Not found: An unknown error occurred.',
            ],
            'invalid id' => [
                'bad_id',
                new SpotifyWebAPIException('Invalid playlist Id', 404),
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                'Not found: Invalid playlist Id',
            ],
            'not found' => [
                '37i9dQZF1DWU4xkXueiKGa',
                new SpotifyWebAPIException('Not found.', 404),
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                'Not found: Not found.',
            ],
            'no token' => [
                '37i9dQZF1DWU4xkXueiKGg',
                new SpotifyWebAPIException('No token provided', 401),
                \Illuminate\Auth\Access\AuthorizationException::class,
                'Unauthorised: No token provided',
            ],
            'unathenticated' => [
                'anything',
                null,
                \Illuminate\Auth\AuthenticationException::class,
                'Unauthenticated.',
                false
            ],
        ];
    }

    /**
     * Test getPlaylistTracks
     *
     * @return void
     * @dataProvider getPlaylistTracksProvider
     */
     public function test_getPlaylistTracks($id, $page, $expectedId, $expectedOptions = null)
    {
        $expectedTracks = ['items' => []];

        // Set up the mocks.
        $session = $this->createMock(Session::class);
        $api = $this->createMock(SpotifyWebAPI::class);

        // If we're not expecting options, perform the getPlaylist call first.
        if (is_null($expectedOptions)) {
            $expectedPlaylist = ['name' => 'Ridonculous', 'tracks' => $expectedTracks];
            $api->expects($this->once())
                ->method('getPlaylist')
                ->with($this->equalTo($expectedId))
                ->will($this->returnValue($expectedPlaylist));
        }
        else {
            $api->expects($this->once())
                ->method('getPlaylistTracks')
                ->with($this->equalTo($expectedId), $this->equalTo($expectedOptions))
                ->will($this->returnValue($expectedTracks));
        }

        $service = new SpotifyService($session, $api);

        // Actually perform the getPlaylist request, if needed.
        if (is_null($expectedOptions)) {
            $service->getPlaylist($id);
        }

        // Get the tracks
        $tracks = $service->getPlaylistTracks($id, $page);

        $this->assertEquals($expectedTracks, $tracks);

        // Perform a second request to ensure the API is only hit once.
        $tracks = $service->getPlaylistTracks($id, $page);
        $this->assertEquals($expectedTracks, $tracks);
    }

    public function getPlaylistTracksProvider()
    {
        return [
            'standard id - page 1' => [
                '19DAgMGSIyeUBEm6a9MTNg',
                1,
                '19DAgMGSIyeUBEm6a9MTNg',
                ['limit' => 100, 'offset' => 0],
            ],
            'spotify uri - page 2' => [
                'spotify:playlist:19DAgMGSIyeUBEm6a9MTNg',
                2,
                '19DAgMGSIyeUBEm6a9MTNg',
                ['limit' => 100, 'offset' => 100],
            ],
            'url - page 3' => [
                'https://open.spotify.com/playlist/19DAgMGSIyeUBEm6a9MTNg?si=9vJkxdbaQhC2ZYtxl8gx8A',
                3,
                '19DAgMGSIyeUBEm6a9MTNg',
                ['limit' => 100, 'offset' => 200],
            ],
            'url - page 4' => [
                'https://open.spotify.com/playlist/19DAgMGSIyeUBEm6a9MTNg',
                4,
                '19DAgMGSIyeUBEm6a9MTNg',
                ['limit' => 100, 'offset' => 300],
            ],
            'non-playlist url - page 5' => [
                'https://open.spotify.com/album/19DAgMGSIyeUBEm6a9MTNg?si=9vJkxdbaQhC2ZYtxl8gx8A',
                5,
                'https://open.spotify.com/album/19DAgMGSIyeUBEm6a9MTNg?si=9vJkxdbaQhC2ZYtxl8gx8A',
                ['limit' => 100, 'offset' => 400],
            ],
            'standard id - page 1 from playlist cache' => [
                '19DAgMGSIyeUBEm6a9MTNg',
                1,
                '19DAgMGSIyeUBEm6a9MTNg',
            ],
        ];
    }

    /**
     * Test getPlaylistTracks - fails.
     *
     * @return void
     * @dataProvider apiFailureProvider
     */
    public function test_getPlaylistTracks_failure($id, $throws, $exceptionClass, $message, $setApi = true)
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($message);

        // Set up the mocks.
        $session = $this->createMock(Session::class);
        $service = null;
        if ($setApi) {
            $api = $this->createMock(SpotifyWebAPI::class);
            $api->expects($this->once())
                ->method('getPlaylistTracks')
                ->with($this->equalTo($id))
                ->will($this->throwException($throws));

            $service = new SpotifyService($session, $api);
        }
        else {
            $service = new SpotifyService($session);
        }

        $service->getPlaylistTracks($id);
    }

    /**
     * Test getAllPlaylistTracks
     *
     * @return void
     */
     public function test_getAllPlaylistTracks()
    {
        $id = 'blah';

        // Set up the mocks.
        $returnMap = [
            [
                $id, ['limit' => 100, 'offset' => 0],
                $this->getSpotifyPlaylistTracks(1),
            ],
            [
                $id, ['limit' => 100, 'offset' => 100],
                $this->getSpotifyPlaylistTracks(2),
            ],
        ];

        $session = $this->createMock(Session::class);
        $api = $this->createMock(SpotifyWebAPI::class);
        $api->expects($this->exactly(2))
            ->method('getPlaylistTracks')
            ->will($this->returnValueMap($returnMap));

        $service = new SpotifyService($session, $api);

        // Get the tracks
        $tracks = $service->getAllPlaylistTracks($id);

        // Assert the result
        $this->assertNotNull($tracks);
        $this->assertArrayHasKey('items', $tracks);
        $this->assertCount(5, $tracks['items']);

        $this->assertEquals('6tvtFyEdNpeurBkT2zNMEL', $tracks['items'][0]['track']['id']);
        $this->assertEquals('Your Love (feat. Jamie Principle)', $tracks['items'][0]['track']['name']);
        $this->assertEquals('27m1soUndRthrAA1ediOXn', $tracks['items'][1]['track']['id']);
        $this->assertEquals('I Heard It Through The Grapevine', $tracks['items'][1]['track']['name']);
        $this->assertEquals('32aSj7Bo0rl3s5fwppu5VP', $tracks['items'][2]['track']['id']);
        $this->assertEquals('Groove Me', $tracks['items'][2]['track']['name']);
        $this->assertEquals('4W9zWGHdrDFygC2Mf1Xmru', $tracks['items'][3]['track']['id']);
        $this->assertEquals('Raspberry Beret', $tracks['items'][3]['track']['name']);
        $this->assertNull($tracks['items'][4]['track']);
    }

    /**
     * Test getUserPlaylists
     *
     * @return void
     * @dataProvider getUserPlaylistsProvider
     */
     public function test_getUserPlaylists($id, $page, $expectedId, $expectedOptions = null)
    {
        $expectedPlaylists = ['items' => []];

        // Set up the mocks.
        $session = $this->createMock(Session::class);
        $api = $this->createMock(SpotifyWebAPI::class);

        // If we're not expecting options, perform the getPlaylist call first.
        $api->expects($this->once())
            ->method('getUserPlaylists')
            ->with($this->equalTo($expectedId), $this->equalTo($expectedOptions))
            ->will($this->returnValue($expectedPlaylists));

        $service = new SpotifyService($session, $api);

        // Actually perform the getPlaylist request, if needed.
        if (is_null($expectedOptions)) {
            $service->getPlaylist($id);
        }

        // Get the playlists
        $playlists = $service->getUserPlaylists($id, $page);

        $this->assertEquals($expectedPlaylists, $playlists);
    }

    public function getUserPlaylistsProvider()
    {
        return [
            'standard id - page 1' => [
                'archy_bold',
                1,
                'archy_bold',
                ['limit' => 50, 'offset' => 0],
            ],
            'spotify uri - page 2' => [
                'spotify:user:archy_bold',
                2,
                'archy_bold',
                ['limit' => 50, 'offset' => 50],
            ],
            'url - page 3' => [
                'https://open.spotify.com/user/archy_bold?si=9vJkxdbaQhC2ZYtxl8gx8A',
                3,
                'archy_bold',
                ['limit' => 50, 'offset' => 100],
            ],
            'url - page 4' => [
                'https://open.spotify.com/user/archy_bold',
                4,
                'archy_bold',
                ['limit' => 50, 'offset' => 150],
            ],
            'non-user url - page 5' => [
                'https://open.spotify.com/album/archy_bold?si=9vJkxdbaQhC2ZYtxl8gx8A',
                5,
                'https://open.spotify.com/album/archy_bold?si=9vJkxdbaQhC2ZYtxl8gx8A',
                ['limit' => 50, 'offset' => 200],
            ],
        ];
    }

    /**
     * Test getUserPlaylists - fails.
     *
     * @return void
     * @dataProvider apiFailureProvider
     */
    public function test_getUserPlaylists_failure($id, $throws, $exceptionClass, $message, $setApi = true)
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($message);

        // Set up the mocks.
        $session = $this->createMock(Session::class);
        $service = null;
        if ($setApi) {
            $api = $this->createMock(SpotifyWebAPI::class);
            $api->expects($this->once())
                ->method('getUserPlaylists')
                ->with($this->equalTo($id))
                ->will($this->throwException($throws));

            $service = new SpotifyService($session, $api);
        }
        else {
            $service = new SpotifyService($session);
        }

        $service->getUserPlaylists($id);
    }

    /**
     * Test getAllUserPlaylists
     *
     * @return void
     */
     public function test_getAllUserPlaylists()
    {
        $id = 'blah';

        // Set up the mocks.
        $returnMap = [
            [
                $id, ['limit' => 50, 'offset' => 0],
                $this->getSpotifyUserPlaylists(1),
            ],
            [
                $id, ['limit' => 50, 'offset' => 50],
                $this->getSpotifyUserPlaylists(2),
            ],
        ];

        $session = $this->createMock(Session::class);
        $api = $this->createMock(SpotifyWebAPI::class);
        $api->expects($this->exactly(2))
            ->method('getUserPlaylists')
            ->will($this->returnValueMap($returnMap));

        $service = new SpotifyService($session, $api);

        // Get the playlists
        $playlists = $service->getAllUserPlaylists($id);

        // Assert the result
        $this->assertNotNull($playlists);
        $this->assertArrayHasKey('items', $playlists);
        $this->assertCount(4, $playlists['items']);

        $this->assertEquals('19DAgMGSIyeUBEm6a9MTNg', $playlists['items'][0]['id']);
        $this->assertEquals('Ridonculous', $playlists['items'][0]['name']);
        $this->assertEquals('2YukjLklW9M2mEcw1MsPrc', $playlists['items'][1]['id']);
        $this->assertEquals('Genosys never happened 2020', $playlists['items'][1]['name']);
        $this->assertEquals('0LlbPNvjlDJe2L8oUkZ7Pt', $playlists['items'][2]['id']);
        $this->assertEquals('blm', $playlists['items'][2]['name']);
        $this->assertEquals('2T6Jo89FVoZpJDlebUNWVO', $playlists['items'][3]['id']);
        $this->assertEquals('Misheard Lyrics', $playlists['items'][3]['name']);
    }

    /**
     * Test parseId function.
     *
     * @return void
     * @dataProvider parseIdProvider
     */
    public function test_parseId($id, $type, $expected)
    {
        $service = $this->app->make(SpotifyService::class);
        $this->assertEquals($expected, $service->parseId($id, $type));
    }

    public function parseIdProvider()
    {
        return $this->getParseIdTests();
    }

    private function getProperty($obj, $prop)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

    public function getParseIdTests($type = null)
    {
        $tests = [
            'standard id' => [
                '19DAgMGSIyeUBEm6a9MTNg',
                'playlist',
                '19DAgMGSIyeUBEm6a9MTNg',
            ],
            'spotify uri' => [
                'spotify:playlist:19DAgMGSIyeUBEm6a9MTNg',
                'playlist',
                '19DAgMGSIyeUBEm6a9MTNg',
            ],
            'spotify uri - non-playlist' => [
                'spotify:user:archy_bold',
                'playlist',
                'spotify:user:archy_bold',
            ],
            'url' => [
                'https://open.spotify.com/playlist/19DAgMGSIyeUBEm6a9MTNg?si=9vJkxdbaQhC2ZYtxl8gx8A',
                'playlist',
                '19DAgMGSIyeUBEm6a9MTNg',
            ],
            'url - 2' => [
                'https://open.spotify.com/playlist/19DAgMGSIyeUBEm6a9MTNg',
                'playlist',
                '19DAgMGSIyeUBEm6a9MTNg',
            ],
            'non-playlist url' => [
                'https://open.spotify.com/album/19DAgMGSIyeUBEm6a9MTNg?si=9vJkxdbaQhC2ZYtxl8gx8A',
                'playlist',
                'https://open.spotify.com/album/19DAgMGSIyeUBEm6a9MTNg?si=9vJkxdbaQhC2ZYtxl8gx8A',
            ],
            'non-valid string' => [
                'anything goes here',
                'playlist',
                'anything goes here',
            ],
            'null playlist' => [
                null,
                'playlist',
                null,
            ],
            'standard track id' => [
                '1yOZzgJxVe1MUWkLNyjLnJ',
                'track',
                '1yOZzgJxVe1MUWkLNyjLnJ',
            ],
            'spotify track uri' => [
                'spotify:track:1yOZzgJxVe1MUWkLNyjLnJ',
                'track',
                '1yOZzgJxVe1MUWkLNyjLnJ',
            ],
            'track url' => [
                'https://open.spotify.com/track/1yOZzgJxVe1MUWkLNyjLnJ',
                'track',
                '1yOZzgJxVe1MUWkLNyjLnJ',
            ],
            'spotify uri - user' => [
                'spotify:user:archy_bold',
                'user',
                'archy_bold',
            ],
            'album url' => [
                'https://open.spotify.com/album/19DAgMGSIyeUBEm6a9MTNg?si=9vJkxdbaQhC2ZYtxl8gx8A',
                'album',
                '19DAgMGSIyeUBEm6a9MTNg',
            ],
        ];

        // Filter if the type is set.
        if (!is_null($type)) {
            $tests = collect($tests)->filter(function ($test) use ($type) {
                return $test[1] == $type;
            })->map(function ($test) {
                unset($test[1]);
                return $test;
            })->toArray();
        }

        return $tests;
    }
}
