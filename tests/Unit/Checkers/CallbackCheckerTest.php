<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit\Checkers;

use Raul3k\BlockDisposable\Core\Checkers\CallbackChecker;
use Raul3k\BlockDisposable\Core\Tests\TestCase;

class CallbackCheckerTest extends TestCase
{
    public function testCallsProvidedCallback(): void
    {
        $disposableDomains = ['mailinator.com', 'guerrillamail.com'];

        $checker = new CallbackChecker(
            fn (string $domain) => in_array($domain, $disposableDomains, true)
        );

        $this->assertTrue($checker->isDomainDisposable('mailinator.com'));
        $this->assertTrue($checker->isDomainDisposable('guerrillamail.com'));
        $this->assertFalse($checker->isDomainDisposable('gmail.com'));
    }

    public function testCallbackReceivesDomain(): void
    {
        $receivedDomain = null;

        $checker = new CallbackChecker(function (string $domain) use (&$receivedDomain) {
            $receivedDomain = $domain;

            return false;
        });

        $checker->isDomainDisposable('test.example.com');

        $this->assertSame('test.example.com', $receivedDomain);
    }

    public function testCanUseArrowFunction(): void
    {
        $checker = new CallbackChecker(
            fn (string $domain) => str_contains($domain, 'temp')
        );

        $this->assertTrue($checker->isDomainDisposable('temp-mail.org'));
        $this->assertTrue($checker->isDomainDisposable('tempmail.com'));
        $this->assertFalse($checker->isDomainDisposable('gmail.com'));
    }

    public function testCanIntegrateWithExternalSystems(): void
    {
        // Simulating Redis-like storage
        $redisSimulator = new class () {
            private array $set = ['mailinator.com', 'temp-mail.org'];

            public function sismember(string $key, string $member): bool
            {
                return in_array($member, $this->set, true);
            }
        };

        $checker = new CallbackChecker(
            fn (string $domain) => $redisSimulator->sismember('disposable_domains', $domain)
        );

        $this->assertTrue($checker->isDomainDisposable('mailinator.com'));
        $this->assertFalse($checker->isDomainDisposable('gmail.com'));
    }
}
