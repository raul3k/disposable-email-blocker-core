<?php

declare(strict_types=1);

namespace Raul3k\DisposableBlocker\Core\Tests\Cache;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Raul3k\DisposableBlocker\Core\Cache\Psr6Adapter;

class Psr6AdapterTest extends TestCase
{
    public function testGetReturnsNullOnMiss(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(false);

        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->method('getItem')->willReturn($item);

        $adapter = new Psr6Adapter($pool);

        $this->assertNull($adapter->get('missing'));
    }

    public function testGetReturnsValueOnHit(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(true);
        $item->method('get')->willReturn('cached_value');

        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->method('getItem')->willReturn($item);

        $adapter = new Psr6Adapter($pool);

        $this->assertSame('cached_value', $adapter->get('key'));
    }

    public function testSetWithoutTtl(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())->method('set')->with('value');
        $item->expects($this->never())->method('expiresAfter');

        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->method('getItem')->willReturn($item);
        $pool->expects($this->once())->method('save')->with($item)->willReturn(true);

        $adapter = new Psr6Adapter($pool);

        $this->assertTrue($adapter->set('key', 'value'));
    }

    public function testSetWithTtl(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())->method('set')->with('value');
        $item->expects($this->once())->method('expiresAfter')->with($this->isInstanceOf(DateInterval::class));

        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->method('getItem')->willReturn($item);
        $pool->expects($this->once())->method('save')->willReturn(true);

        $adapter = new Psr6Adapter($pool);

        $this->assertTrue($adapter->set('key', 'value', 3600));
    }

    public function testSetWithNegativeTtlReturnsFalse(): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->expects($this->never())->method('getItem');

        $adapter = new Psr6Adapter($pool);

        $this->assertFalse($adapter->set('key', 'value', -1));
    }

    public function testSetWithZeroTtl(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())->method('set');
        $item->expects($this->once())->method('expiresAfter');

        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->method('getItem')->willReturn($item);
        $pool->expects($this->once())->method('save')->willReturn(true);

        $adapter = new Psr6Adapter($pool);

        $this->assertTrue($adapter->set('key', 'value', 0));
    }

    public function testHasDelegatesToPool(): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->expects($this->once())
            ->method('hasItem')
            ->with('disposable_key')
            ->willReturn(true);

        $adapter = new Psr6Adapter($pool);

        $this->assertTrue($adapter->has('key'));
    }

    public function testDeleteDelegatesToPool(): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->expects($this->once())
            ->method('deleteItem')
            ->with('disposable_key')
            ->willReturn(true);

        $adapter = new Psr6Adapter($pool);

        $this->assertTrue($adapter->delete('key'));
    }

    public function testClearThrowsBadMethodCallException(): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $adapter = new Psr6Adapter($pool);

        $this->expectException(\BadMethodCallException::class);

        $adapter->clear();
    }

    public function testCustomPrefix(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(true);
        $item->method('get')->willReturn('value');

        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->expects($this->once())
            ->method('getItem')
            ->with('custom_key')
            ->willReturn($item);

        $adapter = new Psr6Adapter($pool, 'custom_');

        $this->assertSame('value', $adapter->get('key'));
    }
}
