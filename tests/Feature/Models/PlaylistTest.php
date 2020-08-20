<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Models;

use ArchyBold\LaravelMusicServices\Playlist;
use ArchyBold\LaravelMusicServices\PlaylistSnapshot;
use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsLaravelRelationships;
use ArchyBold\LaravelMusicServices\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PlaylistTest extends TestCase
{
    use RefreshDatabase, TestsLaravelRelationships;

    /**
     * Test the owner relationship.
     *
     * @return void
     */
    public function test_owner()
    {
        $entity = factory(Playlist::class)->create();
        $this->assertBelongsToRelationship('owner', User::class, $entity);
    }

    /**
     * Test the snapshots relationship.
     *
     * @return void
     */
    public function test_snapshots()
    {
        $entity = factory(Playlist::class)->create();
        $this->assertHasManyRelationship('snapshots', PlaylistSnapshot::class, $entity);
    }

    /**
     * Test latestSnapshot relationship.
     *
     * @return void
     */
    public function test_latestSnapshot()
    {
        $entity = factory(Playlist::class)->create();
        $snapshot1 = factory(PlaylistSnapshot::class)->create();
        $snapshot1->created_at = new Carbon('yesterday');
        $snapshot1->save();
        $snapshot2 = factory(PlaylistSnapshot::class)->create();
        $entity->snapshots()->save($snapshot1);
        $entity->snapshots()->save($snapshot2);

        $this->assertNotNull($entity->latestSnapshot);
        $this->assertEquals($snapshot2->id, $entity->latestSnapshot->id);
    }

    /**
     * Test the uri attribute.
     *
     * @return void
     */
     public function test_uri_attribute()
    {
        $entity = factory(Playlist::class)->create(['vendor_id' => null]);
        $this->assertNull($entity->uri);
        $entity = factory(Playlist::class)->create(['vendor_id' => 'abc123']);
        $this->assertEquals('spotify:playlist:abc123', $entity->uri);
    }
}
