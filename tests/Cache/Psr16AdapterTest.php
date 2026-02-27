<?php

declare(strict_types=1);

namespace Raul3k\DisposableBlocker\Core\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Raul3k\DisposableBlocker\Core\Cache\Psr16Adapter;

class Psr16AdapterTest extends TestCase
{
    public function testGetDelegatesToCache(): void
    {
        $cache = $this->createMock(PsrCacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->with('disposable_key')
            ->willReturn('value');

        $adapter = new Psr16Adapter($cache);

        $this->assertSame('value', $adapter->get('key'));
    }

    public function testSetDelegatesToCache(): void
    {
        $cache = $this->createMock(PsrCacheInterface::class);
        $cache->expects($this->once())
            ->method('set')
            ->with('disposable_key', 'value', 3600)
            ->willReturn(true);

        $adapter = new Psr16Adapter($cache);

        $this->assertTrue($adapter->set('key', 'value', 3600));
    }

    public function testSetWithoutTtl(): void
    {
        $cache = $this->createMock(PsrCacheInterface::class);
        $cache->expects($this->once())
            ->method('set')
            ->with('disposable_key', 'value', null)
            ->willReturn(true);

        $adapter = new Psr16Adapter($cache);

        $this->assertTrue($adapter->set('key', 'value'));
    }

    public function testHasDelegatesToCache(): void
    {
        $cache = $this->createMock(PsrCacheInterface::class);
        $cache->expects($this->once())
            ->method('has')
            ->with('disposable_key')
            ->willReturn(true);

        $adapter = new Psr16Adapter($cache);

        $this->assertTrue($adapter->has('key'));
    }

    public function testDeleteDelegatesToCache(): void
    {
        $cache = $this->createMock(PsrCacheInterface::class);
        $cache->expects($this->once())
            ->method('delete')
            ->with('disposable_key')
            ->willReturn(true);

        $adapter = new Psr16Adapter($cache);

        $this->assertTrue($adapter->delete('key'));
    }

    public function testClearThrowsBadMethodCallException(): void
    {
        $cache = $this->createMock(PsrCacheInterface::class);
        $adapter = new Psr16Adapter($cache);

        $this->expectException(\BadMethodCallException::class);

        $adapter->clear();
    }

    public function testCustomPrefix(): void
    {
        $cache = $this->createMock(PsrCacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->with('custom_key')
            ->willReturn('value');

        $adapter = new Psr16Adapter($cache, 'custom_');

        $this->assertSame('value', $adapter->get('key'));
    }
}
