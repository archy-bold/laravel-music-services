<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Models;

use ArchyBold\LaravelMusicServices\Playlist;
use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsLaravelRelationships;
use ArchyBold\LaravelMusicServices\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class UserTest extends TestCase
{
    use RefreshDatabase, TestsLaravelRelationships;

    /**
     * Test the playlists relationship.
     *
     * @return void
     */
    public function test_playlists()
    {
        $entity = factory(User::class)->create();
        $this->assertHasManyRelationship('playlists', Playlist::class, $entity);
    }

    /**
     * Test the uri attribute.
     *
     * @return void
     */
     public function test_uri_attribute()
    {
        $entity = factory(User::class)->create(['vendor_id' => null]);
        $this->assertNull($entity->uri);
        $entity = factory(User::class)->create(['vendor_id' => 'archy_bold']);
        $this->assertEquals('spotify:user:archy_bold', $entity->uri);
    }
}
