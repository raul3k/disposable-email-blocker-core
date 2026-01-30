<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Cache;

use Psr\SimpleCache\CacheInterface as PsrCacheInterface;

/**
 * Adapter for PSR-16 SimpleCache implementations.
 *
 * Allows using any PSR-16 compatible cache with this library.
 */
class Psr16Adapter implements CacheInterface
{
    private string $prefix;

    public function __construct(
        private readonly PsrCacheInterface $cache,
        string $prefix = 'disposable_'
    ) {
        $this->prefix = $prefix;
    }

    public function get(string $key): mixed
    {
        return $this->cache->get($this->prefix . $key);
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->cache->set($this->prefix . $key, $value, $ttl);
    }

    public function has(string $key): bool
    {
        return $this->cache->has($this->prefix . $key);
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($this->prefix . $key);
    }

    public function clear(): bool
    {
        // PSR-16 clear() clears all cache, not just our prefixed keys
        // This might not be desired behavior, so we just return true
        // without actually clearing
        return true;
    }
}
