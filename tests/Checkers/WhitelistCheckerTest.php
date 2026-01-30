<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Checkers;

use PHPUnit\Framework\TestCase;
use Raul3k\BlockDisposable\Core\Checkers\CallbackChecker;
use Raul3k\BlockDisposable\Core\Checkers\WhitelistChecker;

class WhitelistCheckerTest extends TestCase
{
    public function testWhitelistedDomainBypassesChecker(): void
    {
        $innerChecker = new CallbackChecker(fn () => true); // Always returns true
        $checker = new WhitelistChecker($innerChecker, ['allowed.com']);

        $this->assertFalse($checker->isDomainDisposable('allowed.com'));
    }

    public function testNonWhitelistedDomainUsesInnerChecker(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $checker = new WhitelistChecker($innerChecker, ['allowed.com']);

        $this->assertTrue($checker->isDomainDisposable('blocked.com'));
    }

    public function testWhitelistIsCaseInsensitive(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $checker = new WhitelistChecker($innerChecker, ['ALLOWED.COM']);

        $this->assertFalse($checker->isDomainDisposable('allowed.com'));
        $this->assertFalse($checker->isDomainDisposable('Allowed.Com'));
    }

    public function testParentDomainWhitelist(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $checker = new WhitelistChecker($innerChecker, ['company.com']);

        // Subdomain should also be whitelisted
        $this->assertFalse($checker->isDomainDisposable('sub.company.com'));
        $this->assertFalse($checker->isDomainDisposable('deep.sub.company.com'));
    }

    public function testAddToWhitelist(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $checker = new WhitelistChecker($innerChecker);

        $this->assertTrue($checker->isDomainDisposable('newdomain.com'));

        $checker->addToWhitelist('newdomain.com');

        $this->assertFalse($checker->isDomainDisposable('newdomain.com'));
    }

    public function testRemoveFromWhitelist(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $checker = new WhitelistChecker($innerChecker, ['removeme.com']);

        $this->assertFalse($checker->isDomainDisposable('removeme.com'));

        $checker->removeFromWhitelist('removeme.com');

        $this->assertTrue($checker->isDomainDisposable('removeme.com'));
    }

    public function testIsWhitelisted(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $checker = new WhitelistChecker($innerChecker, ['whitelisted.com']);

        $this->assertTrue($checker->isWhitelisted('whitelisted.com'));
        $this->assertTrue($checker->isWhitelisted('sub.whitelisted.com'));
        $this->assertFalse($checker->isWhitelisted('notwhitelisted.com'));
    }

    public function testGetWhitelist(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $checker = new WhitelistChecker($innerChecker, ['domain1.com', 'domain2.com']);

        $whitelist = $checker->getWhitelist();

        $this->assertCount(2, $whitelist);
        $this->assertContains('domain1.com', $whitelist);
        $this->assertContains('domain2.com', $whitelist);
    }

    public function testClearWhitelist(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $checker = new WhitelistChecker($innerChecker, ['domain.com']);

        $checker->clearWhitelist();

        $this->assertEmpty($checker->getWhitelist());
        $this->assertTrue($checker->isDomainDisposable('domain.com'));
    }

    public function testGetWrappedChecker(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $checker = new WhitelistChecker($innerChecker);

        $this->assertSame($innerChecker, $checker->getWrappedChecker());
    }

    public function testFluentInterface(): void
    {
        $innerChecker = new CallbackChecker(fn () => true);
        $checker = new WhitelistChecker($innerChecker);

        $result = $checker
            ->addToWhitelist('domain1.com')
            ->addToWhitelist('domain2.com')
            ->removeFromWhitelist('domain1.com');

        $this->assertSame($checker, $result);
        $this->assertCount(1, $checker->getWhitelist());
    }
}
