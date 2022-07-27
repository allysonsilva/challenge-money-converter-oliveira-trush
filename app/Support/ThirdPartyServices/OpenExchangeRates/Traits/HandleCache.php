<?php

namespace Support\ThirdPartyServices\OpenExchangeRates\Traits;

use Support\ThirdPartyServices\OpenExchangeRates\Classes\RedisRepository;

trait HandleCache
{
    protected RedisRepository $cache;

    /**
     * Whether of not the exchange rate should be cached
     * after being fetched from the API.
     *
     * @var bool
     */
    protected bool $shouldCache = true;

    /**
     * Whether or not the cache should be busted and a new
     * value should be fetched from the API.
     *
     * @var bool
     */
    protected bool $shouldBustCache = false;

    /**
     * Determine whether if the exchange rate should be
     * cached after it is fetched from the API.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param bool $shouldCache
     *
     * @return $this
     */
    public function shouldCache(bool $shouldCache = true): static
    {
        $this->shouldCache = $shouldCache;

        return $this;
    }

    /**
     * Indicate that the exchange rate should not be cached.
     * Alias for shouldCache(false).
     *
     * @return $this
     */
    public function dontCache(): static
    {
        $this->shouldCache(false);

        return $this;
    }

    /**
     * Determine whether if the cached result (if it
     * exists) should be deleted. This will force
     * a new exchange rate to be fetched from
     * the API.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param bool $bustCache
     *
     * @return $this
     */
    public function shouldBustCache(bool $bustCache = true): static
    {
        $this->shouldBustCache = $bustCache;

        return $this;
    }

    /**
     * Check if the cache operation should be avoided.
     *
     * @return bool
     */
    public function shouldAvoidCache(): bool
    {
        return ! $this->shouldCache;
    }

    /**
     * Attempt to fetch an item (more than likely an exchange rate) from the cache.
     * If it exists, return it. If it has been specified, bust the cache.
     *
     * @param string $cacheKey
     *
     * @return mixed
     */
    private function attemptToResolveFromCache(string $cacheKey): mixed
    {
        if ($this->shouldAvoidCache()) {
            return null;
        }

        if ($this->shouldBustCache) {
            $this->cache->forget($cacheKey);
            $this->shouldBustCache = false;
        } elseif ($cachedValue = $this->cache->getFromCache($cacheKey)) {
            return $cachedValue;
        }

        return null;
    }
}
