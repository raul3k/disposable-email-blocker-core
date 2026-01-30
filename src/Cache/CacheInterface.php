<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Cache;

/**
 * Simple cache interface for disposable email checking.
 *
 * This is a minimal interface. For PSR-6/PSR-16 compatibility,
 * use the provided adapters.
 */
interface CacheInterface
{
    /**
     * Get a value from the cache.
     *
     * @return mixed The cached value or null if not found
     */
    public function get(string $key): mixed;

    /**
     * Store a value in the cache.
     *
     * @param int|null $ttl Time to live in seconds (null = forever)
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Check if a key exists in the cache.
     */
    public function has(string $key): bool;

    /**
     * Delete a key from the cache.
     */
    public function delete(string $key): bool;

    /**
     * Clear all cached values.
     */
    public function clear(): bool;
}
