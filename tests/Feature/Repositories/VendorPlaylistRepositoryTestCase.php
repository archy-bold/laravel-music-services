<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Repositories;

use ArchyBold\LaravelMusicServices\Album;
use ArchyBold\LaravelMusicServices\Playlist;
use ArchyBold\LaravelMusicServices\PlaylistSnapshot;
use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\InteractsWithVendor;
use ArchyBold\LaravelMusicServices\Track;
use ArchyBold\LaravelMusicServices\User;
use ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent\VendorPlaylistRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class VendorPlaylistRepositoryTestCase extends TestCase
{
    use RefreshDatabase, InteractsWithVendor;

    /** @var VendorPlaylistRepository */
    protected $repository;
    /** @var string */
    protected $vendor;
    /** @var array */
    protected $matchedTracks = [];
    /** @var boolean */
    protected $getsIsrcFromPlaylist = true;

    abstract public function getFailureProvider();
    abstract public function createSnapshotFailureProvider();
    abstract public function getExpectedPlaylist();
    abstract public function getExpectedPlaylistSnapshot();
    abstract public function getExpectedPlaylistTracks();
    abstract public function getExampleResponse();
    abstract public function getExampleUserPlaylistsResponse();
    abstract public function getExampleTracksResponse();
    abstract public function getExamplePlaylistCsv();
    abstract public function createMatchingTracks();
    abstract public function buildApiTrack($isrc);

    protected function setUp(): void
    {
        parent::setUp();

        $auth = strpos($this->getName(), 'test_getBuilder') !== 0
            && strpos($this->getName(), 'test_getCsv') !== 0;
        $this->mockVendorService($auth);
    }

    /**
     * Test the get builder function.
     *
     * @return void
     */
    public function test_getBuilder()
    {
        $builder = $this->repository->getBuilder();
        $this->assertNotNull($builder);
        $this->assertInstanceOf(Builder::class, $builder);
    }

    /**
     * Test get - succeeds.
     *
     * @return void
     * @dataProvider getProvider
     */
    public function test_get($returns, $expected, $expectedUser, $exists = false)
    {
        $id = 'sjkdfldsjfsdj';

        // If we're checking for updates, create the existing playlist
        $existing = null;
        if ($exists) {
            $user = factory(User::class)->create([
                'vendor' => $this->vendor,
                'vendor_id' => $expectedUser['vendor_id'],
            ]);
            $existing = factory(Playlist::class)->create([
                'vendor' => $this->vendor,
                'vendor_id' => $id,
                'owner_id' => $user->id,
            ]);
        }

        // Set up the mock service
        $this->mockGetPlaylist($id, $returns);

        // Get the playlist
        $playlist = $this->repository->get($id);

        // Assert it's as expected.
        $this->assertPlaylist($expected, $expectedUser, $playlist);

        // And that the existing was updated
        if ($exists) {
            $this->assertEquals($existing->id, $playlist->id);
            $this->assertEquals($existing->owner->id, $playlist->owner->id);
        }
    }

    public function getProvider()
    {
        return [
            'success' => [
                $this->getExampleResponse(),
                $this->getExpectedPlaylist(),
                $this->getExpectedUser(),
            ],
            'success - empty' => [
                ['foo' => 'bar'],
                [
                    'name' => '',
                    'url' => null,
                    'vendor' => $this->vendor,
                    'vendor_id' => '',
                    'public' => null,
                    'description' => null,
                    'meta' => [],
                    'owner_id' => null,
                ],
                [
                    'name' => '',
                    'meta' => [],
                    'url' => null,
                    'vendor' => 'spotify',
                    'vendor_id' => null,
                ],
            ],
            'success - updates' => [
                $this->getExampleResponse(),
                $this->getExpectedPlaylist(),
                $this->getExpectedUser(),
                true,
            ],
        ];
    }

    /**
     * Test the getCsv function.
     *
     * @return void
     * @dataProvider getCsvProvider
     */
    public function test_getCsv($id, $attrs, $expected, $headers = [], $columns = [], $load = [])
    {
        // Set up the data first.
        if (array_key_exists('owner', $attrs)) {
            $user = factory(User::class)->create($attrs['owner']);
            unset($attrs['owner']);
            $attrs['owner_id'] = $user->id;
        }
        $playlist = factory(Playlist::class)->create(
            collect($attrs)->except('tracks')->toArray()
        );
        if (array_key_exists('tracks', $attrs)) {
            $snapshot = factory(PlaylistSnapshot::class)->create([
                'playlist_id' => $playlist->id,
            ]);
            foreach ($attrs['tracks'] as $trackAttrs) {
                if (array_key_exists('album', $trackAttrs)) {
                    $album = factory(Album::class)->create($trackAttrs['album']);
                    unset($trackAttrs['album']);
                    $trackAttrs['album_id'] = $album->id;
                }
                $track = factory(Track::class)->create($trackAttrs);
                $snapshot->tracks()->attach($track);
            }
        }

        $csv = $this->repository->getCsv($id, $headers, $columns, $load);
        $this->assertEquals($expected, $csv);
    }

    public function getCsvProvider()
    {
        return [
            'from id - no tracks, no headings' => [
                100,
                [
                    'id' => 100,
                    'name' => 'Ridonculous',
                    'vendor' => $this->vendor,
                    'vendor_id' => 'abc123',
                    'url' => 'http://example.org/playlist/abc123',
                    'owner' => [
                        'vendor_id' => 'archy_bold',
                    ],
                ],
                $this->getExamplePlaylistCsv(),
            ],
            'from vendor id - no tracks, no headings' => [
                'abc123',
                [
                    'id' => 100,
                    'name' => 'Ridonculous',
                    'vendor' => $this->vendor,
                    'vendor_id' => 'abc123',
                    'url' => 'http://example.org/playlist/abc123',
                    'owner' => [
                        'vendor_id' => 'archy_bold',
                    ],
                ],
                $this->getExamplePlaylistCsv(),
            ],
            'from id - headings, no tracks' => [
                100,
                [
                    'id' => 100,
                    'name' => 'Ridonculous',
                    'vendor' => $this->vendor,
                    'vendor_id' => 'abc123',
                    'url' => 'http://example.org/playlist/abc123',
                    'owner' => [
                        'vendor_id' => 'archy_bold',
                    ],
                ],
                $this->getExamplePlaylistCsv(['Title', 'Album']),
                ['Title', 'Album'],
            ],
            'from id - headings, tracks' => [
                100,
                [
                    'id' => 100,
                    'name' => 'Ridonculous',
                    'vendor' => $this->vendor,
                    'vendor_id' => 'abc123',
                    'url' => 'http://example.org/playlist/abc123',
                    'owner' => [
                        'vendor_id' => 'archy_bold',
                    ],
                    'tracks' => [
                        [
                            'title' => 'Collossus',
                            'artists' => 'Idles',
                            'album' => [
                                'name' => 'Joy as an Act of Resistance',
                            ],
                        ],
                        [
                            'title' => 'Raspberry Beret',
                            'artists' => 'Prince',
                            'album' => [
                                'name' => '',
                            ],
                        ],
                    ],
                ],
                $this->getExamplePlaylistCsv(['Title', 'Artists', 'Album', 'Missing'], true),
                ['Title', 'Artists', 'Album', 'Missing'],
                ['title', 'artists', 'album.name', 'meta.missing_col'],
                ['latestSnapshot.tracks.album'],
            ],
        ];
    }

    /**
     * Test the getCsv function - not found exception.
     *
     * @return void
     * @dataProvider getCsvNotFoundProvider
     */
    public function test_getCsv_notFound($id)
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Not found.');

        $this->repository->getCsv($id, [], []);
    }

    public function getCsvNotFoundProvider()
    {
        return [
            'from id' => [100],
            'from vendor id' => ['abc123'],
            'from null' => [null],
        ];
    }

    /**
     * Test fail states of get, basically passes error through.
     *
     * @param string $id
     * @param string $throws - The exception the `getPlaylist` function will throw
     * @param string $expectedException - The expected exception
     * @param string $expectedExceptionMessage - The expected exception messsage
     * @return void
     * @dataProvider getFailureProvider
     */
    public function test_get_fails($id, $throws, $expectedException, $expectedExceptionMessage)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        // Set up the mock service
        $return = $throws ? $this->throwException($throws) : $this->returnValue(null);
        $this->mockGetPlaylist($id, $return);

        // Finally get the playlist
        $playlist = $this->repository->get($id);

        if (!$expectedException) {
            $this->assertNull($playlist);
        }
    }

    /**
     * Test createSnapshot - succeeds.
     *
     * @return void
     * @dataProvider createSnapshotProvider
     */
    public function test_createSnapshot($returns, $trackReturns, $expected,
        $expectedTracks = [], $exists = false, $tracksExist = false, $crateMatchingTracks = false)
    {
        $id = 'sjkdfldsjfsdj';

        if ($crateMatchingTracks) {
            $this->createMatchingTracks();
        }

        // If we're checking for updates, create the existing playlist
        $existing = null;
        if ($exists) {
            $existing = factory(Playlist::class)->create([
                'vendor' => $this->vendor,
                'vendor_id' => $id,
            ]);
        }
        $existingTracks = [];
        if ($tracksExist) {
            foreach ($expectedTracks as $track) {
                $existingTracks[] = factory(Track::class)->create([
                    'vendor' => $this->vendor,
                    'vendor_id' => $track['vendor_id'],
                ]);
            }
        }
        $tracksCount = Track::count();

        // Set up the mock service
        $this->mockGetPlaylist($id, $returns, 2, 2);
        $this->service->expects($this->once())
            ->method('getAllPlaylistTracks')
            ->with($this->equalTo($id))
            ->willReturn($trackReturns);
        // If we need to hit the tracks API for the ISRC, mock getTrack too.
        if (!$this->getsIsrcFromPlaylist) {
            $getTrackMap = array_map(function ($expectedTrack) {
                return [$expectedTrack['vendor_id'], $this->buildApiTrack($expectedTrack['isrc'])];
            }, $expectedTracks);
            $this->service->expects($this->exactly(count($expectedTracks)))
                ->method('getTrack')
                ->willReturnMap($getTrackMap);
        }

        // Get the playlist
        $snapshot = $this->repository->createSnapshot($id);

        // Assert it's as expected.
        $this->assertNotNull($snapshot);
        $this->assertTrue($snapshot->exists);
        $this->assertEquals(
            $expected,
            collect($snapshot->toArray())
                ->except('id', 'playlist_id', 'created_at', 'updated_at')
                ->toArray()
        );
        $this->assertEquals(
            $expectedTracks,
            collect($snapshot->load('tracks.album')->tracks->toArray())
                ->map(function ($track) {
                    return collect($track)->except(
                        'id',
                        'track_id',
                        'created_at',
                        'updated_at',
                        'album_id',
                        'album.id',
                        'album.created_at',
                        'album.updated_at',
                        'pivot.track_id',
                        'pivot.playlist_snapshot_id'
                    );
                })
                ->toArray()
        );

        // If there are matchedTracks set, check the track_id of those vendor tracks.
        foreach ($this->matchedTracks as $i => $matchedTrack) {
            if (!is_null($matchedTrack)) {
                $this->assertEquals($matchedTrack->id, $snapshot->tracks->get($i)->track_id);
            }
            else {
                $this->assertNull($snapshot->tracks->get($i)->track_id);
            }
        }

        // And that the existing was updated
        if ($exists) {
            $this->assertEquals($existing->id, $snapshot->playlist->id);
        }
        if ($tracksExist) {
            // No new tracks
            $this->assertEquals($tracksCount, Track::count());
            foreach ($existingTracks as $i => $existingTrack) {
                $this->assertEquals($existingTrack->id, $snapshot->tracks->get($i)->id);
            }
        }
        else {
            // The tracks are new
            $this->assertEquals($tracksCount + count($expectedTracks), Track::count());
        }
    }

    public function createSnapshotProvider()
    {
        return [
            'success' => [
                $this->getExampleResponse(),
                $this->getExampleTracksResponse(),
                $this->getExpectedPlaylistSnapshot(),
                $this->getExpectedPlaylistTracks(),
            ],
            'success - empty' => [
                ['foo' => 'bar'],
                ['items' => []],
                ['num_followers' => null, 'meta' => []],
            ],
            'success - updates' => [
                $this->getExampleResponse(),
                $this->getExampleTracksResponse(),
                $this->getExpectedPlaylistSnapshot(),
                $this->getExpectedPlaylistTracks(),
                true,
            ],
            'success - updates existing tracks' => [
                $this->getExampleResponse(),
                $this->getExampleTracksResponse(),
                $this->getExpectedPlaylistSnapshot(),
                $this->getExpectedPlaylistTracks(),
                true,
                true,
            ],
            'success - matches tracks' => [
                $this->getExampleResponse(),
                $this->getExampleTracksResponse(),
                $this->getExpectedPlaylistSnapshot(),
                $this->getExpectedPlaylistTracks(),
                false,
                false,
                true,
            ],
        ];
    }

    /**
     * Test fail states of createSnapshot, basically passes error through.
     *
     * @param string $id
     * @param string $throws - The exception the `getPlaylist` function will throw
     * @param string $expectedException - The expected exception
     * @param string $expectedExceptionMessage - The expected exception messsage
     * @param string $getPlaylistReturn = null
     * @return void
     * @dataProvider createSnapshotFailureProvider
     */
    public function test_createSnapshot_fails($id, $throws, $expectedException,
        $expectedExceptionMessage, $getPlaylistReturn = null)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        // Set up the mock service
        $return = $throws ? $this->throwException($throws) : $this->returnValue(null);
        if (!is_null($getPlaylistReturn)) {
            $this->service->expects($this->once())
                ->method('getAllPlaylistTracks')
                ->with($this->equalTo($id))
                ->will($return);
            $return = $this->returnValue($getPlaylistReturn);
        }
        $this->mockGetPlaylist($id, $return, 2);

        // Finally get the playlist
        $snapshot = $this->repository->createSnapshot($id);

        if (!$expectedException) {
            $this->assertNull($snapshot);
        }
    }

    /**
     * Test getAllForUser - succeeds.
     *
     * @return void
     * @dataProvider getAllForUserProvider
     */
    public function test_getAllForUser($returns, $expected, $expectedUser = null, $exists = false)
    {
        $id = 'sjkdfldsjfsdj';

        // If we're checking for updates, create the existing playlist
        $existing = [];
        if ($exists) {
            $user = factory(User::class)->create([
                'vendor' => $this->vendor,
                'vendor_id' => $expectedUser['vendor_id'],
            ]);
            foreach ($expected as $attrs) {
                $existing[] = factory(Playlist::class)->create([
                    'vendor' => $this->vendor,
                    'vendor_id' => $attrs['vendor_id'],
                    'owner_id' => $user->id,
                ]);
            }
        }

        // Set up the mock service
        $this->mockGetAllUserPlaylists($id, $returns);

        // Get the playlists
        $playlists = $this->repository->getAllForUser($id);

        // Assert it's as expected.
        $this->assertCount(count($expected), $playlists);

        foreach ($playlists as  $i => $playlist) {
            $this->assertPlaylist($expected[$i], $expectedUser, $playlist);

            // And that the existing was updated
            if ($exists) {
                $this->assertEquals($existing[$i]->id, $playlist->id);
                $this->assertEquals($existing[$i]->owner->id, $playlist->owner->id);
            }
        }
    }

    public function getAllForUserProvider()
    {
        return [
            'success' => [
                $this->getExampleUserPlaylistsResponse(),
                $this->getExpectedUserPlaylists(),
                $this->getExpectedUser(),
            ],
            'success - empty' => [
                ['foo' => 'bar'],
                [],
            ],
            'success - null' => [
                null,
                [],
            ],
            'success - updates' => [
                $this->getExampleUserPlaylistsResponse(),
                $this->getExpectedUserPlaylists(),
                $this->getExpectedUser(),
                true,
            ],
        ];
    }

    /**
     * Test fail states of getAllForUser, basically passes error through.
     *
     * @param string $id
     * @param string $throws - The exception the `getAllUserPlaylists` function will throw
     * @param string $expectedException - The expected exception
     * @param string $expectedExceptionMessage - The expected exception messsage
     * @return void
     * @dataProvider getAllForUserFailureProvider
     */
    public function test_getAllForUser_fails($id, $throws, $expectedException, $expectedExceptionMessage)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        // Set up the mock service
        $return = $throws ? $this->throwException($throws) : $this->returnValue(null);
        $this->mockGetAllUserPlaylists($id, $return);

        // Finally get the playlist
        $playlist = $this->repository->getAllForUser($id);

        if (!$expectedException) {
            $this->assertNull($playlist);
        }
    }

    protected function assertPlaylist($expected, $expectedUser, $playlist)
    {
        $this->assertNotNull($playlist);
        $this->assertTrue($playlist->exists);

        // Check the user
        $this->assertNotNull($playlist->owner);
        $expected['owner_id'] = $playlist->owner->id;
        $this->assertEquals(
            $expectedUser,
            collect($playlist->owner->toArray())
                ->except('id', 'created_at', 'updated_at')
                ->toArray()
        );

        // And finally the playlist itself
        $this->assertEquals(
            $expected,
            collect($playlist->toArray())
                ->except('id', 'owner', 'created_at', 'updated_at')
                ->toArray()
        );
    }
}
