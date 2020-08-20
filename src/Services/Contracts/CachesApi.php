<?php

namespace ArchyBold\LaravelMusicServices\Services\Contracts;

use ArchyBold\LaravelMusicServices\Services\ApiCall;

use Illuminate\Auth\AuthenticationException;

trait CachesApi
{
    /** @var array */
    protected $cache = [];

    /**
     * Retrieve a call from the cache.
     *
     * @param ApiCall $call
     * @return array|null
     */
    protected function retrieveFromCache(ApiCall $call)
    {
        $cacheKey = $call->getCacheKey();
        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }
        return null;
    }

    /**
     * Add a call to the cache.
     *
     * @param ApiCall $call
     * @param array $result
     * @return void
     */
    protected function addToCache(ApiCall $call, $result)
    {
        $cacheKey = $call->getCacheKey();
        $this->cache[$cacheKey] = $result;
    }
}
