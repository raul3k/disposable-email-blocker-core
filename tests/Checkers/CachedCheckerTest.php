<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Checkers;

use PHPUnit\Framework\TestCase;
use Raul3k\BlockDisposable\Core\Cache\ArrayCache;
use Raul3k\BlockDisposable\Core\Checkers\CachedChecker;
use Raul3k\BlockDisposable\Core\Checkers\CallbackChecker;

class CachedCheckerTest extends TestCase
{
    public function testCachesResults(): void
    {
        $callCount = 0;
        $innerChecker = new CallbackChecker(function () use (&$callCount) {
            $callCount++;

            return true;
        });

        $cache = new ArrayCache();
        $checker = new CachedChecker($innerChecker, $cache);

        // First call
        $result1 = $checker->isDomainDisposable('test.com');
        // Second call (should use cache)
        $result2 = $checker->isDomainDisposable('test.com');

        $this->assertTrue($result1);
        $this->assertTrue($result2);
        $this->assertEquals(1, $callCount, 'Inner checker should only be called once');
    }

    public function testCachesDifferentDomainsIndependently(): void
    {
        $results = [
            'disposable.com' => true,
            'legitimate.com' => false,
        ];

        $innerChecker = new CallbackChecker(fn ($domain) => $results[$domain] ?? false);
        $cache = new ArrayCache();
        $checker = new CachedChecker($innerChecker, $cache);

        $this->assertTrue($checker->isDomainDisposable('disposable.com'));
        $this->assertFalse($checker->isDomainDisposable('legitimate.com'));

        // Verify both are cached correctly
        $this->assertTrue($checker->isDomainDisposable('disposable.com'));
        $this->assertFalse($checker->isDomainDisposable('legitimate.com'));
    }

    public function testClearCache(): void
    {
        $callCount = 0;
        $innerChecker = new CallbackChecker(function () use (&$callCount) {
            $callCount++;

            return true;
        });

        $cache = new ArrayCache();
        $checker = new CachedChecker($innerChecker, $cache);

        $checker->isDomainDisposable('test.com');
        $this->assertEquals(1, $callCount);

        $checker->clearCache();

        $checker->isDomainDisposable('test.com');
        $this->assertEquals(2, $callCount, 'Inner checker should be called again after cache clear');
    }

    public function testGetWrappedChecker(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $cache = new ArrayCache();
        $checker = new CachedChecker($innerChecker, $cache);

        $this->assertSame($innerChecker, $checker->getWrappedChecker());
    }

    public function testGetCache(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $cache = new ArrayCache();
        $checker = new CachedChecker($innerChecker, $cache);

        $this->assertSame($cache, $checker->getCache());
    }
}
