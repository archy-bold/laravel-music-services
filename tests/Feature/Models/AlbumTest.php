<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Models;

use ArchyBold\LaravelMusicServices\Album;
use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsLaravelRelationships;
use ArchyBold\LaravelMusicServices\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AlbumTest extends TestCase
{
    use RefreshDatabase, TestsLaravelRelationships;

    /**
     * Test the tracks relationship.
     *
     * @return void
     */
    public function test_tracks()
    {
        $entity = factory(Album::class)->create();
        $this->assertHasManyRelationship('tracks', Track::class, $entity);
    }

    /**
     * Test the uri attribute.
     *
     * @return void
     */
     public function test_uri_attribute()
    {
        $entity = factory(Album::class)->create(['vendor_id' => null]);
        $this->assertNull($entity->uri);
        $entity = factory(Album::class)->create(['vendor_id' => 'abc123']);
        $this->assertEquals('spotify:album:abc123', $entity->uri);
    }
}
