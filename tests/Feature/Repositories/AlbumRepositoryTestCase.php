<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Repositories;

use ArchyBold\LaravelMusicServices\Album;
use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\InteractsWithVendor;
use ArchyBold\LaravelMusicServices\Services\Repositories\Eloquent\AlbumRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class AlbumRepositoryTestCase extends TestCase
{
    use RefreshDatabase, InteractsWithVendor;

    /** @var AlbumRepository */
    protected $repository;
    /** @var string */
    protected $vendor;

    abstract public function getFailureProvider();
    abstract public function getExpectedAlbum();
    abstract public function getExampleAlbumResponse();

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockVendorService($this->getName() != 'test_getBuilder');
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
     * Test get - succeeds.
     *
     * @return void
     * @dataProvider getProvider
     */
    public function test_get($returns, $expected, $exists = false)
    {
        $id = 'sjkdfldsjfsdj';

        // If we're checking for updates, create the existing album
        $existing = null;
        if ($exists) {
            $existing = factory(Album::class)->create([
                'vendor' => $this->vendor,
                'vendor_id' => $id,
            ]);
        }

        // Set up the mock service
        $this->mockGetAlbum($id, $returns);

        // Get the album
        $album = $this->repository->get($id);

        // Assert it's as expected.
        if (is_null($expected)) {
            $this->assertNull($album);
        }
        else {
            $this->assertNotNull($album);
            $this->assertEquals(
                $expected,
                collect($album->toArray())->except([
                    'id',
                    'created_at',
                    'updated_at',
                ])->toArray()
            );

            // And that the existing was updated
            if ($exists) {
                $this->assertEquals($existing->id, $album->id);
            }
        }
    }

    public function getProvider()
    {
        return [
            'success' => [
                $this->getExampleAlbumResponse(),
                $this->getExpectedAlbum(),
            ],
            'success - empty' => [
                ['foo' => 'bar'],
                [
                    'name' => '',
                    'artists' => '',
                    'upc' => '',
                    'release_date' => null,
                    'type' => 'album',
                    'meta' => [],
                    'url' => null,
                    'vendor' => 'spotify',
                    'vendor_id' => null,
                ],
            ],
            'success - updates' => [
                $this->getExampleAlbumResponse(),
                $this->getExpectedAlbum(),
                true,
            ],
        ];
    }

    /**
     * Test fail states of get, basically passes error through.
     *
     * @param string $id
     * @param string $throws - The exception the `get` function will throw
     * @param string $expectedException - The expected exception
     * @param string $expectedExceptionMessage - The expected exception messsage
     * @return void
     * @dataProvider getFailureProvider
     */
    public function test_get_fails($id, $throws, $expectedException, $expectedExceptionMessage)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        // Set up the mock service
        $return = $throws ? $this->throwException($throws) : $this->returnValue(null);
        $this->mockGetAlbum($id, $return);

        // Finally get the album
        $album = $this->repository->get($id);

        if (!$expectedException) {
            $this->assertNull($album);
        }
    }
}
