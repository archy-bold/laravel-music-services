<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Repositories;

use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\InteractsWithVendor;
use ArchyBold\LaravelMusicServices\Track;
use ArchyBold\LaravelMusicServices\TrackInformation;
use ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent\TrackRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TrackRepositoryTestCase extends TestCase
{
    use RefreshDatabase, InteractsWithVendor;

    /** @var TrackRepository */
    protected $repository;
    /** @var string */
    protected $vendor;

    abstract public function getAudioFeaturesFailureProvider();
    abstract public function getExpectedAudioFeatures();
    abstract public function getExampleAudioFeaturesResponse();

    protected function setUp(): void
    {
        parent::setUp();

        $auth = strpos($this->getName(), 'test_getBuilder') !== 0
            && strpos($this->getName(), 'test_instance') !== 0;

        $this->mockVendorService($auth);
    }

    /**
     * Check the app provider gives the instance.
     *
     * @return void
     */
    public function test_instance()
    {
        $className = get_class($this->repository);
        $repo = $this->app->make($className);
        $this->assertNotNull($repo);
        $this->assertInstanceOf($className, $repo);
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
     * Test getAudioFeatures - succeeds.
     *
     * @return void
     * @dataProvider getAudioFeaturesProvider
     */
    public function test_getAudioFeatures($returns, $expected, $createTrack = true, $exists = false)
    {
        $id = 'sjkdfldsjfsdj';

        // If we're checking for updates, create the existing track information
        $track = null;
        if ($createTrack) {
            $track = factory(Track::class)->create([
                'vendor' => $this->vendor,
                'vendor_id' => $id,
            ]);
            $expected['track_id'] = $track->id;
        }
        $existing = null;
        if ($exists) {
            $existing = factory(TrackInformation::class)->create([
                'vendor' => $this->vendor,
                'type' => $expected['type'],
                'track_id' => $track->id,
            ]);
        }

        // Set up the mock service
        $this->mockGetTrackAudioFeatures($id, $returns);

        // Get the audioFeatures
        $audioFeatures = $this->repository->getAudioFeatures($id);

        // Assert it's as expected.
        if (is_null($expected)) {
            $this->assertNull($audioFeatures);
        }
        else {
            $this->assertNotNull($audioFeatures);
            $this->assertEquals(
                $expected,
                collect($audioFeatures->toArray())->except([
                    'id',
                    'created_at',
                    'updated_at',
                ])->toArray()
            );

            // And that the existing was updated
            if ($exists) {
                $this->assertEquals($existing->id, $audioFeatures->id);
            }
        }
    }

    public function getAudioFeaturesProvider()
    {
        return [
            'no vendor track' => [
                $this->getExampleAudioFeaturesResponse(),
                null,
                false,
            ],
            'success' => [
                $this->getExampleAudioFeaturesResponse(),
                $this->getExpectedAudioFeatures(),
            ],
            'success - empty' => [
                ['foo' => 'bar'],
                [
                    'type' => 'audio_features',
                    'vendor' => $this->vendor,
                    'meta' => [],
                    'camelot_code' => null,
                    'duration_s' => null,
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
                $this->getExampleAudioFeaturesResponse(),
                $this->getExpectedAudioFeatures(),
                true,
                true,
            ],
        ];
    }

    /**
     * Test fail states of getAudioFeatures, basically passes error through.
     *
     * @param string $id
     * @param string $throws - The exception the `getAudioFeatures` function will throw
     * @param string $expectedException - The expected exception
     * @param string $expectedExceptionMessage - The expected exception messsage
     * @return void
     * @dataProvider getAudioFeaturesFailureProvider
     */
    public function test_getAudioFeatures_fails($id, $throws, $expectedException, $expectedExceptionMessage)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        // Set up the mock service
        $return = $throws ? $this->throwException($throws) : $this->returnValue(null);
        $this->mockGetTrackAudioFeatures($id, $return);

        // Finally getAudioFeatures the playlist
        $playlist = $this->repository->getAudioFeatures($id);

        if (!$expectedException) {
            $this->assertNull($playlist);
        }
    }
}
