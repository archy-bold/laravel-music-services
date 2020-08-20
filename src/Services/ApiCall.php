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
    /** @string */
    protected $cacheKey;

    public function __construct($function, $id, $options = [])
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

    public static function generateCacheKey($function, $id, $options = [])
    {
        if (is_array($id)) {
            $id = serialize($id);
        }
        return $function . '_' . $id . '_' . serialize($options);
    }
}
