<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Repositories;

use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsSpotifyApi;
use ArchyBold\LaravelMusicServices\Services\Repositories\Spotify\VendorTrackRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SpotifyVendorTrackRepositoryTest extends VendorTrackRepositoryTestCase
{
    use TestsSpotifyApi;

    /** @var string */
    protected $vendor = 'spotify';

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new VendorTrackRepository($this->service);
    }

    public function getAudioFeaturesFailureProvider()
    {
        return [
            'null returned from getTrackAudioFeatures' => [
                'id',
                null,
                null,
                null,
            ],
            'empty id' => [
                '',
                new NotFoundHttpException('Not found: An unknown error occurred.'),
                NotFoundHttpException::class,
                'Not found: An unknown error occurred.',
            ],
            'invalid id' => [
                'bad_id',
                new NotFoundHttpException('Not found: Invalid track Id'),
                NotFoundHttpException::class,
                'Not found: Invalid track Id',
            ],
            'not found' => [
                '37i9dQZF1DWU4xkXueiKGa',
                new NotFoundHttpException('Not found: Not found.'),
                NotFoundHttpException::class,
                'Not found: Not found.',
            ],
            'no token' => [
                '37i9dQZF1DWU4xkXueiKGg',
                new AuthorizationException('Unauthorised: No token provided', 401),
                AuthorizationException::class,
                'Unauthorised: No token provided',
            ],
            'unathenticated' => [
                'anything',
                new AuthenticationException('Unauthenticated.'),
                AuthenticationException::class,
                'Unauthenticated.',
            ],
        ];
    }

    public function getExampleAudioFeaturesResponse()
    {
        return $this->getSpotifyAudioFeatures()['audio_features'][0];
    }
}
