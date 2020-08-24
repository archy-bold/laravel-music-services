<?php

namespace ArchyBold\LaravelMusicServices\Services;

class ApiCall
{
    /** @var string */
    protected $function;
    /** @var string */
    protected $id;
    /** @var array */
    protected $options;
    /** @var string */
    protected $cacheKey;
    /** @var boolean */
    protected $cacheable = true;
    /** @var boolean */
    protected $requiresId = true;

    public function __construct($function, $id = null, $options = [])
    {
        $this->function = $function;
        $this->id = $id;
        $this->options = $options;

        // Set the cache key
        $this->cacheKey = self::generateCacheKey($function, $id, $options);
    }

    /**
     * Get the function.
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Get the ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get the cache key.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Get whether this call should be cached.
     *
     * @return boolean
     */
    public function isCacheable()
    {
        return $this->cacheable;
    }

    /**
     * Set whether this call should be cached.
     *
     * @param boolan
     */
    public function setCacheable($cacheable)
    {
        $this->cacheable = $cacheable;
    }

    /**
     * Get whether this call requires an ID.
     *
     * @return boolean
     */
    public function requiresId()
    {
        return $this->requiresId;
    }

    /**
     * Set whether this call requires an ID.
     *
     * @param boolan
     */
    public function setRequiresId($requiresId)
    {
        $this->requiresId = $requiresId;
    }

    public static function generateCacheKey($function, $id, $options = [])
    {
        if (is_array($id)) {
            $id = serialize($id);
        }
        return $function . '_' . $id . '_' . serialize($options);
    }
}
