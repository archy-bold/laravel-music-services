<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Models;

use ArchyBold\LaravelMusicServices\Album;
use ArchyBold\LaravelMusicServices\PlaylistSnapshot;
use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsLaravelRelationships;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsPlaylists;
use ArchyBold\LaravelMusicServices\Track;
use ArchyBold\LaravelMusicServices\TrackInformation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TrackTest extends TestCase
{
    use RefreshDatabase, TestsLaravelRelationships, TestsPlaylists;

    /** @var Track */
    protected $entity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = factory(Track::class)->create();
    }

    /**
     * Test onCurrentPlaylist scope.
     *
     * @return void
     */
    public function test_onCurrentPlaylist()
    {
        // Create a playlist with snapshots.
        $playlist = $this->getFilledPlaylist(true);
        // Set the second snapshot as older.
        $playlist->snapshots->get(1)->update(['created_at' => new Carbon('-1 week')]);
        // Add a track to it.
        $playlist->snapshots->get(1)->tracks()->save($this->entity);

        // Expect the first snapshot's tracks to be in the list.
        $expected = $playlist->snapshots->first()->tracks->pluck('id')->toArray();

        $tracks = Track::onCurrentPlaylist()->get();
        $this->assertCount(2, $tracks);
        $this->assertEquals($expected[0], $tracks->first()->id);
        $this->assertEquals($expected[1], $tracks->get(1)->id);
    }

    /**
     * Test playlistSnapshots relationship.
     *
     * @return void
     */
    public function test_playlistSnapshots()
    {
        $this->assertBelongsToManyRelationship('playlistSnapshots', PlaylistSnapshot::class, $this->entity);

        // Test the pivot data too.
        $playlist1 = $this->entity->playlistSnapshots->get(0);
        $playlist2 = $this->entity->playlistSnapshots->get(1);
        $this->entity->playlistSnapshots()->detach();
        $this->entity->playlistSnapshots()->save($playlist1, [
            'order' => 0,
            'added_at' => new Carbon('2019-01-01'),
            'meta' => ['added_by' => 'Jimi'],
        ]);
        $this->entity->playlistSnapshots()->save($playlist2, [
            'order' => 1,
            'added_at' => new Carbon('2018-01-01'),
            'meta' => ['added_by' => 'Jonny'],
        ]);

        $this->entity = $this->entity->fresh();
        // Check the pivot data.
        $this->assertEquals(0, $this->entity->playlistSnapshots->get(0)->pivot->order);
        $this->assertEquals(new Carbon('2019-01-01'), $this->entity->playlistSnapshots->get(0)->pivot->added_at);
        $this->assertEquals(['added_by' => 'Jimi'], $this->entity->playlistSnapshots->get(0)->pivot->meta);
        $this->assertEquals(1, $this->entity->playlistSnapshots->get(1)->pivot->order);
        $this->assertEquals(new Carbon('2018-01-01'), $this->entity->playlistSnapshots->get(1)->pivot->added_at);
        $this->assertEquals(['added_by' => 'Jonny'], $this->entity->playlistSnapshots->get(1)->pivot->meta);
    }

    /**
     * Test currentSnapshots relationship.
     *
     * @return void
     */
    public function test_currentSnapshots()
    {
        // Add the vendor track to two snapshots, one current, one old.
        $playlist1 = $this->getFilledPlaylist(true);
        $playlist1->update(['vendor_id' => 'def456']);
        $currentSnapshot = $playlist1->snapshots->get(0);
        $currentSnapshot->update(['created_at' => new Carbon('+1 week')]);
        $playlist2 = $this->getFilledPlaylist(true);
        $nonCurrentSnapshot = $playlist2->snapshots->get(1);
        $nonCurrentSnapshot->update(['created_at' => new Carbon('-1 week')]);
        $currentSnapshot->tracks()->attach($this->entity->id);
        $nonCurrentSnapshot->tracks()->attach($this->entity->id);

        $currentSnapshots = $this->entity->currentSnapshots;

        $this->assertCount(1, $currentSnapshots);
        $this->assertEquals($currentSnapshot->id, $currentSnapshots->first()->id);
    }

    /**
     * Test the album relationship.
     *
     * @return void
     */
    public function test_album()
    {
        $this->assertBelongsToRelationship('album', Album::class, $this->entity);
    }

    /**
     * Test the trackInformation relationship.
     *
     * @return void
     */
    public function test_trackInformation()
    {
        $this->assertHasManyRelationship(
            'trackInformation',
            TrackInformation::class,
            $this->entity,
            ['vendor' => 'spotify'],
            ['vendor' => 'napster'],
        );
    }

    /**
     * Test the audioFeatures relationship.
     *
     * @return void
     */
    public function test_audioFeatures()
    {
        $this->assertHasOneRelationship(
            'audioFeatures',
            TrackInformation::class,
            $this->entity,
            ['vendor' => 'spotify', 'type' => 'audio_features'],
            ['vendor' => 'napster', 'type' => 'audio_features'],
        );
    }

    /**
     * Test the uri attribute.
     *
     * @return void
     */
     public function test_uri_attribute()
    {
        $entity = factory(Track::class)->create(['vendor_id' => null]);
        $this->assertNull($entity->uri);
        $entity = factory(Track::class)->create(['vendor_id' => 'abc123']);
        $this->assertEquals('spotify:track:abc123', $entity->uri);
    }
}
