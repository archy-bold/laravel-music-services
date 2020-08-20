<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Repositories;

use ArchyBold\LaravelMusicServices\Services\Repositories\Spotify\VendorPlaylistRepository;
use ArchyBold\LaravelMusicServices\Services\Spotify\SpotifyService;
use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsSpotifyApi;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SpotifyVendorPlaylistRepositoryTest extends VendorPlaylistRepositoryTestCase
{
    use TestsSpotifyApi;

    /** @var string */
    protected $vendor = 'spotify';

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new VendorPlaylistRepository($this->service);
    }

    public function getFailureProvider()
    {
        return [
            'null returned from getPlaylist' => [
                'id',
                null,
                null,
                null,
            ],
            'empty id' => [
                '',
                new NotFoundHttpException('Not found: An unknown error occurred.'),
                NotFoundHttpException::class,
                'Not found: An unknown error occurred.',
            ],
            'invalid id' => [
                'bad_id',
                new NotFoundHttpException('Not found: Invalid playlist Id'),
                NotFoundHttpException::class,
                'Not found: Invalid playlist Id',
            ],
            'not found' => [
                '37i9dQZF1DWU4xkXueiKGa',
                new NotFoundHttpException('Not found: Not found.'),
                NotFoundHttpException::class,
                'Not found: Not found.',
            ],
            'no token' => [
                '37i9dQZF1DWU4xkXueiKGg',
                new AuthorizationException('Unauthorised: No token provided', 401),
                AuthorizationException::class,
                'Unauthorised: No token provided',
            ],
            'unathenticated' => [
                'anything',
                new AuthenticationException('Unauthenticated.'),
                AuthenticationException::class,
                'Unauthenticated.',
            ],
        ];
    }

    public function createSnapshotFailureProvider()
    {
        return array_merge($this->getFailureProvider(), [
            'null returned from getPlaylistTracks' => [
                'id',
                null,
                null,
                null,
                $this->getExampleResponse(),
            ],
            'failure on getPlaylistTracks' => [
                'sdfdsfsdfsd',
                new NotFoundHttpException('Not found: An unknown error occurred.'),
                NotFoundHttpException::class,
                'Not found: An unknown error occurred.',
                $this->getExampleResponse(),
            ],
        ]);
    }

    public function getAllForUserFailureProvider()
    {
        return [
            'empty id' => [
                '',
                new NotFoundHttpException('Not found: An unknown error occurred.'),
                NotFoundHttpException::class,
                'Not found: An unknown error occurred.',
            ],
            'not found' => [
                '37i9dQZF1DWU4xkXueiKGa',
                new NotFoundHttpException('Not found: Not found.'),
                NotFoundHttpException::class,
                'Not found: Not found.',
            ],
            'no token' => [
                '37i9dQZF1DWU4xkXueiKGg',
                new AuthorizationException('Unauthorised: No token provided', 401),
                AuthorizationException::class,
                'Unauthorised: No token provided',
            ],
            'unathenticated' => [
                'anything',
                new AuthenticationException('Unauthenticated.'),
                AuthenticationException::class,
                'Unauthenticated.',
            ],
        ];
    }

    public function getExampleResponse()
    {
        return $this->getSpotifyPlaylist();
    }

    public function getExampleUserPlaylistsResponse()
    {
        return $this->getSpotifyAllUserPlaylists();
    }

    public function getExampleTracksResponse()
    {
        return [
            "href" => "https://api.spotify.com/v1/playlists/19DAgMGSIyeUBEm6a9MTNg/tracks?offset=0&limit=4",
            "items" => array_merge(
                $this->getSpotifyPlaylistTracks(1)['items'],
                $this->getSpotifyPlaylistTracks(2)['items']
            ),
            "limit" => 4,
            "next" => null,
            "offset" => 0,
            "previous" => null,
            "total" => 4
        ];
    }

    public function getExamplePlaylistCsv($headings = [], $tracks = false)
    {
        $csv = [
            ['Playlist', 'Ridonculous'],
            ['User', 'archy_bold'],
            ['URI', 'spotify:playlist:abc123'],
            ['URL', 'http://example.org/playlist/abc123'],
            $headings,
        ];
        if ($tracks) {
            $csv[] = ['Collossus', 'Idles', 'Joy as an Act of Resistance', ''];
            $csv[] = ['Raspberry Beret', 'Prince', '', ''];
        }
        return $csv;
    }

    public function createMatchingTracks()
    {
        // $track1 = factory(Track::class)->create([
        //     'title' => 'Your Love (feat. Jamie Principle)',
        //     'isrc' => 'GBBLG0100312',
        // ]);
        // $track2 = factory(Track::class)->create([
        //     'title' => 'Raspberry Beret',
        //     'isrc' => 'USWB19902876',
        // ]);
        // $track3 = factory(Track::class)->create([
        //     'title' => 'Raspberry Beret',
        //     'isrc' => 'USWB19902876',
        // ]);
        // $album1 = factory(Album::class)->create([
        //     'title' => 'Raspberry Beret',
        // ]);
        // $album2 = factory(Album::class)->create([
        //     'title' => 'Raspberry Beret / She\'s Always In My Hair',
        // ]);
        // $track2->albums()->attach($album1->id, ['disc' => 1, 'track' => 1]);
        // $track3->albums()->attach($album2->id, ['disc' => 1, 'track' => 1]);
        // $this->matchedTracks = [$track1, null, null, $track3];
    }

    public function buildApiTrack($isrc)
    {
        return ['external_ids' => ['isrc' => 'FR9W11920848']];
    }
}
