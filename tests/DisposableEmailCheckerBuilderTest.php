<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests;

use PHPUnit\Framework\TestCase;
use Raul3k\BlockDisposable\Core\Cache\ArrayCache;
use Raul3k\BlockDisposable\Core\Checkers\CachedChecker;
use Raul3k\BlockDisposable\Core\Checkers\ChainChecker;
use Raul3k\BlockDisposable\Core\Checkers\WhitelistChecker;
use Raul3k\BlockDisposable\Core\DisposableEmailChecker;
use Raul3k\BlockDisposable\Core\DisposableEmailCheckerBuilder;

class DisposableEmailCheckerBuilderTest extends TestCase
{
    public function testBuilderFromStaticMethod(): void
    {
        $builder = DisposableEmailChecker::builder();

        $this->assertInstanceOf(DisposableEmailCheckerBuilder::class, $builder);
    }

    public function testBuildWithDefaults(): void
    {
        $checker = DisposableEmailChecker::builder()->build();

        $this->assertInstanceOf(DisposableEmailChecker::class, $checker);
        $this->assertTrue($checker->isDisposable('test@mailinator.com'));
    }

    public function testBuildWithBundledDomains(): void
    {
        $checker = DisposableEmailChecker::builder()
            ->withBundledDomains()
            ->build();

        $this->assertTrue($checker->isDisposable('test@mailinator.com'));
        $this->assertFalse($checker->isDisposable('test@gmail.com'));
    }

    public function testBuildWithPatternDetection(): void
    {
        $checker = DisposableEmailChecker::builder()
            ->withPatternDetection()
            ->build();

        $this->assertTrue($checker->isDisposable('test@tempmail.com'));
        $this->assertTrue($checker->isDisposable('test@10minutemail.com'));
    }

    public function testBuildWithWhitelist(): void
    {
        $checker = DisposableEmailChecker::builder()
            ->withBundledDomains()
            ->withWhitelist(['mailinator.com'])
            ->build();

        // Whitelisted, so not disposable
        $this->assertFalse($checker->isDisposable('test@mailinator.com'));

        // Checker should be wrapped in WhitelistChecker
        $this->assertInstanceOf(WhitelistChecker::class, $checker->getChecker());
    }

    public function testBuildWithCache(): void
    {
        $cache = new ArrayCache();

        $checker = DisposableEmailChecker::builder()
            ->withBundledDomains()
            ->withCache($cache)
            ->build();

        // Checker should be wrapped in CachedChecker
        $this->assertInstanceOf(CachedChecker::class, $checker->getChecker());

        // First call
        $checker->isDisposable('test@mailinator.com');

        // Cache should have the result
        $this->assertTrue($cache->has('domain_check_mailinator.com'));
    }

    public function testBuildWithCallback(): void
    {
        $checker = DisposableEmailChecker::builder()
            ->withCallback(fn ($domain) => $domain === 'custom.com')
            ->build();

        $this->assertTrue($checker->isDisposable('test@custom.com'));
        $this->assertFalse($checker->isDisposable('test@other.com'));
    }

    public function testBuildWithMultipleCheckers(): void
    {
        $checker = DisposableEmailChecker::builder()
            ->withBundledDomains()
            ->withPatternDetection()
            ->withCallback(fn ($domain) => $domain === 'custom-disposable.com')
            ->build();

        // Inner checker should be ChainChecker
        $innerChecker = $checker->getChecker();
        $this->assertInstanceOf(ChainChecker::class, $innerChecker);

        // All sources should work
        $this->assertTrue($checker->isDisposable('test@mailinator.com')); // bundled
        $this->assertTrue($checker->isDisposable('test@tempmail.com'));   // pattern
        $this->assertTrue($checker->isDisposable('test@custom-disposable.com')); // callback
    }

    public function testBuildWithAllFeatures(): void
    {
        $cache = new ArrayCache();

        $checker = DisposableEmailChecker::builder()
            ->withBundledDomains()
            ->withPatternDetection()
            ->withWhitelist(['allowed.com'])
            ->withCache($cache, ttl: 7200)
            ->build();

        // Should be CachedChecker wrapping WhitelistChecker wrapping ChainChecker
        $this->assertInstanceOf(CachedChecker::class, $checker->getChecker());

        // Whitelist should work
        $this->assertFalse($checker->isDisposable('test@allowed.com'));

        // Normal check should work
        $this->assertTrue($checker->isDisposable('test@mailinator.com'));
    }

    public function testFluentInterface(): void
    {
        $builder = DisposableEmailChecker::builder();

        $result = $builder
            ->withBundledDomains()
            ->withPatternDetection()
            ->withWhitelist(['test.com'])
            ->withCache(new ArrayCache());

        $this->assertSame($builder, $result);
    }
}
