<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit\Checkers;

use InvalidArgumentException;
use Raul3k\BlockDisposable\Core\Checkers\CallbackChecker;
use Raul3k\BlockDisposable\Core\Checkers\ChainChecker;
use Raul3k\BlockDisposable\Core\Tests\TestCase;

class ChainCheckerTest extends TestCase
{
    public function testReturnsTrueIfAnyCheckerReturnsTrue(): void
    {
        $checker1 = new CallbackChecker(fn (string $d) => $d === 'mailinator.com');
        $checker2 = new CallbackChecker(fn (string $d) => $d === 'temp-mail.org');

        $chain = new ChainChecker([$checker1, $checker2]);

        $this->assertTrue($chain->isDomainDisposable('mailinator.com'));
        $this->assertTrue($chain->isDomainDisposable('temp-mail.org'));
    }

    public function testReturnsFalseIfAllCheckersReturnFalse(): void
    {
        $checker1 = new CallbackChecker(fn (string $d) => $d === 'mailinator.com');
        $checker2 = new CallbackChecker(fn (string $d) => $d === 'temp-mail.org');

        $chain = new ChainChecker([$checker1, $checker2]);

        $this->assertFalse($chain->isDomainDisposable('gmail.com'));
    }

    public function testStopsAtFirstTrueResult(): void
    {
        $checker1Called = false;
        $checker2Called = false;

        $checker1 = new CallbackChecker(function (string $d) use (&$checker1Called) {
            $checker1Called = true;

            return true;
        });

        $checker2 = new CallbackChecker(function (string $d) use (&$checker2Called) {
            $checker2Called = true;

            return true;
        });

        $chain = new ChainChecker([$checker1, $checker2]);
        $chain->isDomainDisposable('test.com');

        $this->assertTrue($checker1Called);
        $this->assertFalse($checker2Called);
    }

    public function testAddCheckerAppendsToChain(): void
    {
        $checker1 = new CallbackChecker(fn (string $d) => $d === 'mailinator.com');

        $chain = new ChainChecker([$checker1]);

        $this->assertFalse($chain->isDomainDisposable('temp-mail.org'));

        $checker2 = new CallbackChecker(fn (string $d) => $d === 'temp-mail.org');
        $chain->addChecker($checker2);

        $this->assertTrue($chain->isDomainDisposable('temp-mail.org'));
    }

    public function testGetCheckersReturnsAllCheckers(): void
    {
        $checker1 = new CallbackChecker(fn (string $d) => false);
        $checker2 = new CallbackChecker(fn (string $d) => false);

        $chain = new ChainChecker([$checker1, $checker2]);

        $this->assertCount(2, $chain->getCheckers());
    }

    public function testThrowsExceptionForEmptyCheckerArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one checker is required');

        new ChainChecker([]);
    }

    public function testThrowsExceptionForInvalidChecker(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');

        new ChainChecker([new \stdClass()]); // @phpstan-ignore-line
    }

    public function testPreservesCheckerOrder(): void
    {
        $order = [];

        $checker1 = new CallbackChecker(function (string $d) use (&$order) {
            $order[] = 1;

            return false;
        });

        $checker2 = new CallbackChecker(function (string $d) use (&$order) {
            $order[] = 2;

            return false;
        });

        $checker3 = new CallbackChecker(function (string $d) use (&$order) {
            $order[] = 3;

            return false;
        });

        $chain = new ChainChecker([$checker1, $checker2, $checker3]);
        $chain->isDomainDisposable('test.com');

        $this->assertSame([1, 2, 3], $order);
    }
}
