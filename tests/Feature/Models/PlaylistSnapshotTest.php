<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Models;

use ArchyBold\LaravelMusicServices\Playlist;
use ArchyBold\LaravelMusicServices\PlaylistSnapshot;
use ArchyBold\LaravelMusicServices\Track;
use ArchyBold\LaravelMusicServices\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsLaravelRelationships;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsPlaylists;

class PlaylistSnapshotTest extends TestCase
{
    use RefreshDatabase, TestsLaravelRelationships, TestsPlaylists;

    /** @var PlaylistSnapshot */
    protected $entity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = factory(PlaylistSnapshot::class)->create();
    }

    /**
     * Test the playlist relationship.
     *
     * @return void
     */
    public function test_playlist()
    {
        $this->assertBelongsToRelationship('playlist', Playlist::class, $this->entity);
    }

    /**
     * Test tracks relationship.
     *
     * @return void
     */
    public function test_tracks()
    {
        $this->assertBelongsToManyRelationship('tracks', Track::class, $this->entity);

        // Test the pivot data too.
        $track1 = $this->entity->tracks->get(0);
        $track2 = $this->entity->tracks->get(1);
        $this->entity->tracks()->detach();
        $this->entity->tracks()->save($track2, [
            'order' => 0,
            'added_at' => new Carbon('2019-01-01'),
            'meta' => ['added_by' => 'Jimi'],
        ]);
        $this->entity->tracks()->save($track1, [
            'order' => 1,
            'added_at' => new Carbon('2018-01-01'),
            'meta' => ['added_by' => 'Jonny'],
        ]);

        $this->entity = $this->entity->fresh();
        // Check the order.
        $this->assertCount(2, $this->entity->tracks);
        $this->assertEquals($track2->id, $this->entity->tracks->get(0)->id);
        $this->assertEquals($track1->id, $this->entity->tracks->get(1)->id);

        // And the pivot data.
        $this->assertEquals(0, $this->entity->tracks->get(0)->pivot->order);
        $this->assertEquals(new Carbon('2019-01-01'), $this->entity->tracks->get(0)->pivot->added_at);
        $this->assertEquals(['added_by' => 'Jimi'], $this->entity->tracks->get(0)->pivot->meta);
        $this->assertEquals(1, $this->entity->tracks->get(1)->pivot->order);
        $this->assertEquals(new Carbon('2018-01-01'), $this->entity->tracks->get(1)->pivot->added_at);
        $this->assertEquals(['added_by' => 'Jonny'], $this->entity->tracks->get(1)->pivot->meta);
    }

    /**
     * Test the currentSnapshot scopes
     *
     * @return void
     */
    public function test_scopeCurrent()
    {
        // Set up two playlists with two snapshots
        $playlist1 = $this->getFilledPlaylist(true);
        $playlist1->update(['vendor_id' => 'def456']);
        $currentSnapshot1 = $playlist1->snapshots->get(0);
        $currentSnapshot1->update(['created_at' => new Carbon('+1 week')]);
        $playlist2 = $this->getFilledPlaylist(true);
        $currentSnapshot2 = $playlist2->snapshots->get(1);
        $currentSnapshot2->update(['created_at' => new Carbon('+1 week')]);

        $currentSnapshots = PlaylistSnapshot::current()->get();

        $this->assertCount(3, $currentSnapshots);
        $this->assertEquals($this->entity->id, $currentSnapshots->get(0)->id);
        $this->assertEquals($currentSnapshot1->id, $currentSnapshots->get(1)->id);
        $this->assertEquals($currentSnapshot2->id, $currentSnapshots->get(2)->id);
    }
}
