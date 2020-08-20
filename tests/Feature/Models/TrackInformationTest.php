<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Models;

use ArchyBold\LaravelMusicServices\Track;
use ArchyBold\LaravelMusicServices\TrackInformation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsLaravelRelationships;

class TrackInformationTest extends TestCase
{
    use TestsLaravelRelationships, RefreshDatabase;

    /** @var Track */
    protected $entity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = factory(TrackInformation::class)->create();
    }

    /**
     * Test audioFeatures scope.
     *
     * @return void
     */
    public function test_audioFeatures()
    {
        $this->entity->delete();
        $spotifyRandom = factory(TrackInformation::class)->create([
            'type' => 'random_info',
            'vendor' => 'spotify',
        ]);
        $spotifyFeatures = factory(TrackInformation::class)->create([
            'type' => TrackInformation::AUDIO_FEATURES,
            'vendor' => 'spotify',
        ]);
        $napsterFeatures = factory(TrackInformation::class)->create([
            'type' => TrackInformation::AUDIO_FEATURES,
            'vendor' => 'napster',
        ]);

        // Assert the function works
        $info = TrackInformation::audioFeatures()->get();
        $this->assertCount(2, $info);
        $this->assertEquals($napsterFeatures->id, $info->first()->id);
        $this->assertEquals($spotifyFeatures->id, $info->get(1)->id);
        $info = TrackInformation::audioFeatures('napster')->get();
        $this->assertCount(1, $info);
        $this->assertEquals($napsterFeatures->id, $info->first()->id);
        $info = TrackInformation::audioFeatures('spotify')->get();
        $this->assertCount(1, $info);
        $this->assertEquals($spotifyFeatures->id, $info->first()->id);
    }

    /**
     * Test the track relationship.
     *
     * @return void
     */
    public function test_track()
    {
        $this->assertBelongsToRelationship('track', Track::class, $this->entity);
    }

    /**
     * Test the function to get the duration_s attribute.
     *
     * @return void
     * @dataProvider getDurationSAttributeProvider
     */
    public function test_getDurationSAttribute($expected, $attrs)
    {
        $entity = factory(TrackInformation::class)->create($attrs);
        $this->assertEquals($expected, $entity->duration_s);
    }

    public function getDurationSAttributeProvider()
    {
        return [
            'empty' => [null, []],
            'duration_ms set int' => [100, ['meta' => ['duration_ms' => 100000]]],
            'duration_ms set float' => [234.765, ['meta' => ['duration_ms' => 234765]]],
            'null for string' => [null, ['meta' => ['duration_ms' => 'a string']]],
        ];
    }

    /**
     * Test the function to get the camelot code attribute.
     *
     * @return void
     * @dataProvider getCamelotCodeAttributeProvider
     */
    public function test_getCamelotCodeAttribute($expected, $attrs)
    {
        $entity = factory(TrackInformation::class)->create($attrs);
        $this->assertEquals($expected, $entity->camelot_code);
    }

    public function getCamelotCodeAttributeProvider()
    {
        return [
            'not spotify' => [null, ['vendor' => 'napster']],
            'not audio features' => [null, ['type' => 'random']],
            'unknown key' => [null, ['meta' => ['key' => -1, 'mode' => 0]]],
            '1A' => ['1A', ['meta' => ['key' => 8, 'mode' => 0]]],
            '2A' => ['2A', ['meta' => ['key' => 3, 'mode' => 0]]],
            '3A' => ['3A', ['meta' => ['key' => 10, 'mode' => 0]]],
            '4A' => ['4A', ['meta' => ['key' => 5, 'mode' => 0]]],
            '5A' => ['5A', ['meta' => ['key' => 0, 'mode' => 0]]],
            '6A' => ['6A', ['meta' => ['key' => 7, 'mode' => 0]]],
            '7A' => ['7A', ['meta' => ['key' => 2, 'mode' => 0]]],
            '8A' => ['8A', ['meta' => ['key' => 9, 'mode' => 0]]],
            '9A' => ['9A', ['meta' => ['key' => 4, 'mode' => 0]]],
            '10A' => ['10A', ['meta' => ['key' => 11, 'mode' => 0]]],
            '11A' => ['11A', ['meta' => ['key' => 6, 'mode' => 0]]],
            '12A' => ['12A', ['meta' => ['key' => 1, 'mode' => 0]]],
            '1B' => ['1B', ['meta' => ['key' => 11, 'mode' => 1]]],
            '2B' => ['2B', ['meta' => ['key' => 6, 'mode' => 1]]],
            '3B' => ['3B', ['meta' => ['key' => 1, 'mode' => 1]]],
            '4B' => ['4B', ['meta' => ['key' => 8, 'mode' => 1]]],
            '5B' => ['5B', ['meta' => ['key' => 3, 'mode' => 1]]],
            '6B' => ['6B', ['meta' => ['key' => 10, 'mode' => 1]]],
            '7B' => ['7B', ['meta' => ['key' => 5, 'mode' => 1]]],
            '8B' => ['8B', ['meta' => ['key' => 0, 'mode' => 1]]],
            '9B' => ['9B', ['meta' => ['key' => 7, 'mode' => 1]]],
            '10B' => ['10B', ['meta' => ['key' => 2, 'mode' => 1]]],
            '11B' => ['11B', ['meta' => ['key' => 9, 'mode' => 1]]],
            '12B' => ['12B', ['meta' => ['key' => 4, 'mode' => 1]]],
        ];
    }
}
