<?php

namespace ArchyBold\LaravelMusicServices\Tests\Traits;

use ArchyBold\LaravelMusicServices\Album;
use ArchyBold\LaravelMusicServices\Playlist;
use ArchyBold\LaravelMusicServices\PlaylistSnapshot;
use ArchyBold\LaravelMusicServices\Track;
use Carbon\Carbon;

trait TestsPlaylists
{
    protected function getFilledPlaylist($withSnapshots = false)
    {
        $playlist = factory(Playlist::class)->create([
            'name' => 'Ridonculous',
            'url' => 'http://example.org',
            'vendor_id' => 'abc123',
            'vendor' => 'spotify',
            'public' => true,
            'description' => 'description here',
            'meta' => [
                'owner' => ['name' => 'Simon Archer'],
                'attribute' => true,
            ],
        ]);
        if ($withSnapshots) {
            $snapshot1 = factory(PlaylistSnapshot::class)->create([
                'playlist_id' => $playlist->id,
                'num_followers' => 1000,
                'meta' => ['duration' => 1000],
            ]);
            $snapshot2 = factory(PlaylistSnapshot::class)->create([
                'playlist_id' => $playlist->id,
            ]);
            $album1 = factory(Album::class)->create(['name' => 'Brutalism']);
            $track1 = factory(Track::class)->create([
                'title' => 'Mother',
                'artists' => 'Idles',
                'isrc' => 'GB17123456',
                'album_id' => $album1->id,
            ]);
            $album2 = factory(Album::class)->create(['name' => 'Ordinary Pleasure']);
            $track2 = factory(Track::class)->create([
                'title' => 'Freelance',
                'artists' => 'Toro Y Moi',
                'isrc' => 'US19123456',
                'album_id' => $album2->id,
            ]);
            $snapshot1->tracks()->save($track1, [
                'order' => 0,
                'added_at' => new Carbon('2019-01-01'),
                'meta' => ['added_by' => 'John'],
            ]);
            $snapshot1->tracks()->save($track2, [
                'order' => 1,
            ]);
        }
        return $playlist;
    }
}
