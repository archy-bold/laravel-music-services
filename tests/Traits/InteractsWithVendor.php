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

    protected function mockGetPlaylist($id, $returns, $parseIdTimes = 1, $getPlaylistTimes = 1)
    {
        if (is_array($returns)) {
            $returns = $this->returnValue($returns);
        }
        $this->service->expects($this->exactly($getPlaylistTimes))
            ->method('getPlaylist')
            ->with($this->equalTo($id))
            ->will($returns);
        if ($parseIdTimes > 0) {
            $this->service->expects($this->exactly($parseIdTimes))
                ->method('parseId')
                ->with($this->equalTo($id), $this->equalTo('playlist'))
                ->will($this->returnValue($id));
        }
    }

    protected function mockCreatePlaylist($attrs, $returns, $createPlaylistTimes = 1)
    {
        if (is_array($returns)) {
            $returns = $this->returnValue($returns);
        }
        $this->service->expects($this->exactly($createPlaylistTimes))
            ->method('createPlaylist')
            ->with($this->equalTo($attrs))
            ->will($returns);
    }

    protected function mockGetAllPlaylistTracks($id, $returns, $getAllPlaylistTracksTimes = 1)
    {
        if (is_array($returns)) {
            $returns = $this->returnValue($returns);
        }
        $this->service->expects($this->exactly($getAllPlaylistTracksTimes))
            ->method('getAllPlaylistTracks')
            ->with($this->equalTo($id))
            ->will($returns);
    }

    protected function mockGetAllUserPlaylists($userId, $returns, $parseIdTimes = 1, $getPlaylistTimes = 1)
    {
        if (is_array($returns)) {
            $returns = $this->returnValue($returns);
        }
        if (is_null($returns)) {
            $returns = $this->returnValue(null);
        }
        $this->service->expects($this->exactly($getPlaylistTimes))
            ->method('getAllUserPlaylists')
            ->with($this->equalTo($userId))
            ->will($returns);
        if ($parseIdTimes > 0) {
            $this->service->expects($this->exactly($parseIdTimes))
                ->method('parseId')
                ->with($this->equalTo($userId), $this->equalTo('user'))
                ->will($this->returnValue($userId));
        }
    }

    protected function mockGetTrackAudioFeatures($id, $returns, $parseIdTimes = 1, $getTrackAudioFeaturesTimes = 1)
    {
        if (is_array($returns)) {
            $returns = $this->returnValue($returns);
        }
        $this->service->expects($this->exactly($getTrackAudioFeaturesTimes))
            ->method('getTrackAudioFeatures')
            ->with($this->equalTo($id))
            ->will($returns);
        if ($parseIdTimes > 0) {
            $this->service->expects($this->exactly($parseIdTimes))
                ->method('parseId')
                ->with($this->equalTo($id), $this->equalTo('track'))
                ->will($this->returnValue($id));
        }
    }
}
