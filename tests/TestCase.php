<?php

namespace ArchyBold\LaravelMusicServices\Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return ['ArchyBold\LaravelMusicServices\MusicServicesServiceProvider'];
    }
}
