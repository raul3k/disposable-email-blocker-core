<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Raul3k\BlockDisposable\Core\Cache\ArrayCache;

class ArrayCacheTest extends TestCase
{
    private ArrayCache $cache;

    protected function setUp(): void
    {
        $this->cache = new ArrayCache();
    }

    public function testSetAndGet(): void
    {
        $this->cache->set('key', 'value');

        $this->assertEquals('value', $this->cache->get('key'));
    }

    public function testGetReturnsNullForMissingKey(): void
    {
        $this->assertNull($this->cache->get('nonexistent'));
    }

    public function testHas(): void
    {
        $this->assertFalse($this->cache->has('key'));

        $this->cache->set('key', 'value');

        $this->assertTrue($this->cache->has('key'));
    }

    public function testDelete(): void
    {
        $this->cache->set('key', 'value');

        $this->assertTrue($this->cache->delete('key'));
        $this->assertFalse($this->cache->has('key'));
    }

    public function testDeleteNonexistentKey(): void
    {
        $this->assertTrue($this->cache->delete('nonexistent'));
    }

    public function testClear(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');

        $this->assertTrue($this->cache->clear());
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }

    public function testTtlExpiration(): void
    {
        $this->cache->set('key', 'value', 1); // 1 second TTL

        $this->assertTrue($this->cache->has('key'));

        sleep(2);

        $this->assertFalse($this->cache->has('key'));
        $this->assertNull($this->cache->get('key'));
    }

    public function testCount(): void
    {
        $this->assertEquals(0, $this->cache->count());

        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');

        $this->assertEquals(2, $this->cache->count());
    }

    public function testStoresDifferentTypes(): void
    {
        $this->cache->set('string', 'hello');
        $this->cache->set('int', 42);
        $this->cache->set('float', 3.14);
        $this->cache->set('bool', true);
        $this->cache->set('array', ['a', 'b']);
        $this->cache->set('null', null);

        $this->assertEquals('hello', $this->cache->get('string'));
        $this->assertEquals(42, $this->cache->get('int'));
        $this->assertEquals(3.14, $this->cache->get('float'));
        $this->assertTrue($this->cache->get('bool'));
        $this->assertEquals(['a', 'b'], $this->cache->get('array'));
        // Note: null values cannot be distinguished from missing keys
    }
}
