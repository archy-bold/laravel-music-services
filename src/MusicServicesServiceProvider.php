<?php

namespace ArchyBold\LaravelMusicServices;

use ArchyBold\LaravelMusicServices\Services\Repositories\Spotify\VendorPlaylistRepository as SpotifyVendorPlaylistRepository;
use ArchyBold\LaravelMusicServices\Services\Repositories\Spotify\VendorTrackRepository as SpotifyVendorTrackRepository;
use ArchyBold\LaravelMusicServices\Services\Spotify\SpotifyService;
use Illuminate\Support\ServiceProvider;

class MusicServicesServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'archy_bold');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'archy_bold');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-music-services.php', 'laravel-music-services');

        // Register the service the package provides.
        $this->bindSpotifyServices();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravel-music-services'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravel-music-services.php' => config_path('laravel-music-services.php'),
        ], 'laravel-music-services.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/archy_bold'),
        ], 'laravel-music-services.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/archy_bold'),
        ], 'laravel-music-services.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/archy_bold'),
        ], 'laravel-music-services.views');*/

        // Registering package commands.
        // $this->commands([]);
    }

    public function bindSpotifyServices()
    {
        $this->app->bind(SpotifyService::class, function ($app) {
            return new SpotifyService(new \SpotifyWebAPI\Session(
                config('services.spotify.client_id'),
                config('services.spotify.client_secret')
            ));
        });
        $this->app->bind(SpotifyVendorPlaylistRepository::class, function ($app) {
            return new SpotifyVendorPlaylistRepository(
                $app->make(SpotifyService::class)
            );
        });
        $this->app->bind(SpotifyVendorTrackRepository::class, function ($app) {
            return new SpotifyVendorTrackRepository(
                $app->make(SpotifyService::class)
            );
        });
    }
}
