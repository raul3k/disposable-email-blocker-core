<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Cache;

/**
 * In-memory array cache implementation.
 *
 * Useful for single-request caching. Data is lost when the process ends.
 */
class ArrayCache implements CacheInterface
{
    /** @var array<string, array{value: mixed, expires: int|null}> */
    private array $cache = [];

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->cache[$key]['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $this->cache[$key] = [
            'value' => $value,
            'expires' => $ttl !== null ? time() + $ttl : null,
        ];

        return true;
    }

    public function has(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        $entry = $this->cache[$key];
        if ($entry['expires'] !== null && $entry['expires'] < time()) {
            unset($this->cache[$key]);

            return false;
        }

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key]);

        return true;
    }

    public function clear(): bool
    {
        $this->cache = [];

        return true;
    }

    /**
     * Get the number of items in the cache.
     */
    public function count(): int
    {
        $this->removeExpired();

        return count($this->cache);
    }

    /**
     * Remove all expired entries.
     */
    private function removeExpired(): void
    {
        $now = time();
        foreach ($this->cache as $key => $entry) {
            if ($entry['expires'] !== null && $entry['expires'] < $now) {
                unset($this->cache[$key]);
            }
        }
    }
}
