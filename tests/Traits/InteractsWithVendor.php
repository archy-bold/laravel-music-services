<?php

namespace ArchyBold\LaravelMusicServices\Tests\Traits;

use ArchyBold\LaravelMusicServices\Services\Contracts\VendorService;

trait InteractsWithVendor
{
    /** @var VendorService */
    protected $service;

    public function mockVendorService($auth = true, $times = 1)
    {
        $this->service = $this->createMock(VendorService::class);
        if ($auth) {
            $this->service->expects($this->exactly($times))->method('authenticate');
        }
    }

    protected function mockMapParseId($map, $times = null)
    {
        if (is_null($times)) {
            $times = count($map);
        }
        $this->service->expects($this->exactly($times))
            ->method('parseId')
            ->will($this->returnValueMap($map));
    }

    protected function mockGetPlaylist($id, $returns, $parseIdTimes = 1, $times = 1)
    {
        $this->mockVendorFunction('getPlaylist', [
            'id' => $id,
            'type' => 'playlist',
            'returns' => $returns,
            'times' => $times,
            'parse_id_times' => $parseIdTimes,
        ]);
    }

    protected function mockCreatePlaylist($attrs, $returns, $times = 1)
    {
        $this->mockVendorFunction('createPlaylist', [
            'id' => $attrs,
            'returns' => $returns,
            'times' => $times,
        ]);
    }

    protected function mockGetAllPlaylistTracks($id, $returns, $times = 1)
    {
        $this->mockVendorFunction('getAllPlaylistTracks', [
            'id' => $id,
            'returns' => $returns,
            'times' => $times,
        ]);
    }

    protected function mockGetAllUserPlaylists($userId, $returns, $parseIdTimes = 1, $times = 1)
    {
        $this->mockVendorFunction('getAllUserPlaylists', [
            'id' => $userId,
            'type' => 'user',
            'returns' => $returns,
            'times' => $times,
            'parse_id_times' => $parseIdTimes,
        ]);
    }

    protected function mockGetTrackAudioFeatures($id, $returns, $parseIdTimes = 1, $times = 1)
    {
        $this->mockVendorFunction('getTrackAudioFeatures', [
            'id' => $id,
            'type' => 'track',
            'returns' => $returns,
            'times' => $times,
            'parse_id_times' => $parseIdTimes,
        ]);
    }

    protected function mockGetAlbum($id, $returns, $parseIdTimes = 1, $times = 1)
    {
        $this->mockVendorFunction('getAlbum', [
            'id' => $id,
            'type' => 'album',
            'returns' => $returns,
            'times' => $times,
            'parse_id_times' => $parseIdTimes,
        ]);
    }

    protected function mockAddPlaylistTracks($args, $returns, $parseIdTimes = 1, $times = 1)
    {
        $this->mockVendorFunction('addPlaylistTracks', [
            'id' => $args[0],
            'args' => $args,
            'type' => 'playlist',
            'returns' => $returns,
            'times' => $times,
            'parse_id_times' => $parseIdTimes,
        ]);
    }

    protected function mockVendorFunction($function, $options)
    {
        $id = $options['id'] ?? '';
        $type = $options['type'] ?? '';
        $returns = $options['returns'] ?? null;
        $functionTimes = $options['times'] ?? 1;
        $parseIdTimes = $options['parse_id_times'] ?? 0;
        $with = [$this->equalTo($id)];

        // If there are multiple arguments
        if (isset($options['args'])) {
            $with = array_map(function ($arg) {
                return $this->equalTo($arg);
            }, $options['args']);
        }

        if (is_array($returns)) {
            $returns = $this->returnValue($returns);
        }
        if (is_null($returns)) {
            $returns = $this->returnValue(null);
        }
        $this->service->expects($this->exactly($functionTimes))
            ->method($function)
            ->with(...$with)
            ->will($returns);
        if ($parseIdTimes > 0) {
            $this->service->expects($this->exactly($parseIdTimes))
                ->method('parseId')
                ->with($this->equalTo($id), $this->equalTo($type))
                ->will($this->returnValue($id));
        }
    }
}
