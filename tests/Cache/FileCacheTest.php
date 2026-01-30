<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Raul3k\BlockDisposable\Core\Cache\FileCache;

class FileCacheTest extends TestCase
{
    private string $cacheDir;

    private FileCache $cache;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/disposable_cache_test_' . uniqid();
        $this->cache = new FileCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        // Clean up cache directory
        $this->cache->clear();
        if (is_dir($this->cacheDir)) {
            rmdir($this->cacheDir);
        }
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

    public function testCreatesDirectoryIfNotExists(): void
    {
        $newDir = sys_get_temp_dir() . '/new_cache_dir_' . uniqid();

        $this->assertDirectoryDoesNotExist($newDir);

        new FileCache($newDir);

        $this->assertDirectoryExists($newDir);

        // Cleanup
        rmdir($newDir);
    }

    public function testGarbageCollection(): void
    {
        $this->cache->set('expired', 'value', 1);
        $this->cache->set('valid', 'value', 3600);

        sleep(2);

        $removed = $this->cache->gc();

        $this->assertEquals(1, $removed);
        $this->assertFalse($this->cache->has('expired'));
        $this->assertTrue($this->cache->has('valid'));
    }

    public function testPersistsBetweenInstances(): void
    {
        $this->cache->set('persistent', 'value');

        // Create new instance with same directory
        $newCache = new FileCache($this->cacheDir);

        $this->assertEquals('value', $newCache->get('persistent'));
    }

    public function testStoresComplexTypes(): void
    {
        $array = ['nested' => ['data' => [1, 2, 3]]];
        $this->cache->set('array', $array);

        $this->assertEquals($array, $this->cache->get('array'));
    }
}
