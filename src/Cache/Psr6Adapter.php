<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Cache;

use DateInterval;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Adapter for PSR-6 CacheItemPool implementations.
 *
 * Allows using any PSR-6 compatible cache with this library.
 */
class Psr6Adapter implements CacheInterface
{
    private string $prefix;

    public function __construct(
        private readonly CacheItemPoolInterface $pool,
        string $prefix = 'disposable_'
    ) {
        $this->prefix = $prefix;
    }

    public function get(string $key): mixed
    {
        $item = $this->pool->getItem($this->prefix . $key);

        if (!$item->isHit()) {
            return null;
        }

        return $item->get();
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $item = $this->pool->getItem($this->prefix . $key);
        $item->set($value);

        if ($ttl !== null) {
            $item->expiresAfter(new DateInterval('PT' . $ttl . 'S'));
        }

        return $this->pool->save($item);
    }

    public function has(string $key): bool
    {
        return $this->pool->hasItem($this->prefix . $key);
    }

    public function delete(string $key): bool
    {
        return $this->pool->deleteItem($this->prefix . $key);
    }

    public function clear(): bool
    {
        // PSR-6 clear() clears all cache, not just our prefixed keys
        // This might not be desired behavior, so we just return true
        // without actually clearing
        return true;
    }
}
