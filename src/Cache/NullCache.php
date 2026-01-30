<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Cache;

/**
 * Null cache implementation that doesn't cache anything.
 * Useful for testing or when caching is not needed.
 */
class NullCache implements CacheInterface
{
    public function get(string $key): mixed
    {
        return null;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return true;
    }

    public function has(string $key): bool
    {
        return false;
    }

    public function delete(string $key): bool
    {
        return true;
    }

    public function clear(): bool
    {
        return true;
    }
}
