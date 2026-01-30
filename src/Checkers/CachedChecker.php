<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Checkers;

use Raul3k\BlockDisposable\Core\Cache\CacheInterface;

/**
 * Decorator that adds caching to any checker.
 *
 * Caches check results to avoid repeated lookups for the same domain.
 */
class CachedChecker implements CheckerInterface
{
    private const CACHE_PREFIX = 'domain_check_';

    /**
     * @param CheckerInterface $checker The underlying checker to wrap
     * @param CacheInterface $cache The cache implementation to use
     * @param int|null $ttl Cache TTL in seconds (null = forever)
     */
    public function __construct(
        private readonly CheckerInterface $checker,
        private readonly CacheInterface $cache,
        private readonly ?int $ttl = 3600
    ) {
    }

    public function isDomainDisposable(string $normalizedDomain): bool
    {
        $cacheKey = self::CACHE_PREFIX . $normalizedDomain;

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return (bool) $cached;
        }

        $result = $this->checker->isDomainDisposable($normalizedDomain);
        $this->cache->set($cacheKey, $result ? 1 : 0, $this->ttl);

        return $result;
    }

    /**
     * Clear the cache.
     */
    public function clearCache(): bool
    {
        return $this->cache->clear();
    }

    /**
     * Get the underlying checker.
     */
    public function getWrappedChecker(): CheckerInterface
    {
        return $this->checker;
    }

    /**
     * Get the cache instance.
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }
}
