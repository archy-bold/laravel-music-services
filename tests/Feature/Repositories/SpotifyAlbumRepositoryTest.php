<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Repositories;

use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Tests\Traits\TestsSpotifyApi;
use ArchyBold\LaravelMusicServices\Services\Repositories\Spotify\AlbumRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SpotifyAlbumRepositoryTest extends AlbumRepositoryTestCase
{
    use TestsSpotifyApi;

    /** @var string */
    protected $vendor = 'spotify';

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AlbumRepository($this->service);
    }

    public function getFailureProvider()
    {
        return [
            'null returned from getAlbum' => [
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
                new NotFoundHttpException('Not found: Invalid album Id'),
                NotFoundHttpException::class,
                'Not found: Invalid album Id',
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

    public function getExampleAlbumResponse()
    {
        return $this->getSpotifyAlbum();
    }
}
