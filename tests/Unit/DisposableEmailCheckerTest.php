<?php

declare(strict_types=1);

namespace Raul3k\DisposableBlocker\Core\Tests\Unit;

use Raul3k\DisposableBlocker\Core\Checkers\CallbackChecker;
use Raul3k\DisposableBlocker\Core\Checkers\FileChecker;
use Raul3k\DisposableBlocker\Core\CheckResult;
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;
use Raul3k\DisposableBlocker\Core\DomainNormalizer;
use Raul3k\DisposableBlocker\Core\Exceptions\InvalidDomainException;
use Raul3k\DisposableBlocker\Core\Tests\TestCase;

class DisposableEmailCheckerTest extends TestCase
{
    private ?string $tempFile = null;

    protected function tearDown(): void
    {
        if ($this->tempFile !== null) {
            $this->removeTempFile($this->tempFile);
            $this->tempFile = null;
        }
    }

    public function testCreateReturnsInstance(): void
    {
        $checker = DisposableEmailChecker::create();

        $this->assertInstanceOf(DisposableEmailChecker::class, $checker);
    }

    public function testCreateWithCustomChecker(): void
    {
        $customChecker = new CallbackChecker(fn ($d) => $d === 'test.com');

        $checker = DisposableEmailChecker::create($customChecker);

        $this->assertTrue($checker->isDisposable('user@test.com'));
        $this->assertFalse($checker->isDisposable('user@other.com'));
    }

    public function testIsDisposableReturnsTrueForDisposableEmail(): void
    {
        $this->tempFile = $this->createTempFile("mailinator.com\ntemp-mail.org");

        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $this->assertTrue($checker->isDisposable('user@mailinator.com'));
        $this->assertTrue($checker->isDisposable('test@temp-mail.org'));
    }

    public function testIsDisposableReturnsFalseForNonDisposableEmail(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');

        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $this->assertFalse($checker->isDisposable('user@gmail.com'));
    }

    public function testIsDisposableHandlesSubdomains(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');

        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $this->assertTrue($checker->isDisposable('user@sub.mailinator.com'));
    }

    public function testIsDisposableThrowsForInvalidEmail(): void
    {
        $checker = DisposableEmailChecker::create();

        $this->expectException(InvalidDomainException::class);

        $checker->isDisposable('notanemail');
    }

    public function testIsDisposableSafeReturnsFalseForInvalidEmail(): void
    {
        $checker = DisposableEmailChecker::create();

        $this->assertFalse($checker->isDisposableSafe('notanemail'));
        $this->assertFalse($checker->isDisposableSafe(''));
    }

    public function testIsDisposableSafeWorksForValidEmail(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');

        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $this->assertTrue($checker->isDisposableSafe('user@mailinator.com'));
        $this->assertFalse($checker->isDisposableSafe('user@gmail.com'));
    }

    public function testIsDomainDisposableChecksDirectly(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');

        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $this->assertTrue($checker->isDomainDisposable('mailinator.com'));
        $this->assertTrue($checker->isDomainDisposable('sub.mailinator.com'));
        $this->assertFalse($checker->isDomainDisposable('gmail.com'));
    }

    public function testIsDomainDisposableSafeReturnsFalseForInvalid(): void
    {
        $checker = DisposableEmailChecker::create();

        $this->assertFalse($checker->isDomainDisposableSafe(''));
    }

    public function testNormalizeReturnsNormalizedDomain(): void
    {
        $checker = DisposableEmailChecker::create();

        $this->assertSame('gmail.com', $checker->normalize('user@gmail.com'));
        $this->assertSame('gmail.com', $checker->normalize('user@GMAIL.COM'));
        $this->assertSame('gmail.com', $checker->normalize('user@sub.gmail.com'));
    }

    public function testGetCheckerReturnsChecker(): void
    {
        $customChecker = new CallbackChecker(fn ($d) => false);

        $checker = DisposableEmailChecker::create($customChecker);

        $this->assertSame($customChecker, $checker->getChecker());
    }

    public function testGetNormalizerReturnsNormalizer(): void
    {
        $checker = DisposableEmailChecker::create();

        $this->assertInstanceOf(DomainNormalizer::class, $checker->getNormalizer());
    }

    public function testCreateWithNormalizerUsesProvidedNormalizer(): void
    {
        $normalizer = new DomainNormalizer();

        $checker = DisposableEmailChecker::createWithNormalizer($normalizer);

        $this->assertSame($normalizer, $checker->getNormalizer());
    }

    public function testBundledListContainsCommonDisposableDomains(): void
    {
        $checker = DisposableEmailChecker::create();

        $this->assertTrue($checker->isDisposable('user@mailinator.com'));
        $this->assertTrue($checker->isDisposable('user@guerrillamail.com'));
        $this->assertTrue($checker->isDisposable('user@10minutemail.com'));
        $this->assertTrue($checker->isDisposable('user@temp-mail.org'));
        $this->assertTrue($checker->isDisposable('user@yopmail.com'));
    }

    public function testBundledListDoesNotContainLegitDomains(): void
    {
        $checker = DisposableEmailChecker::create();

        $this->assertFalse($checker->isDisposable('user@gmail.com'));
        $this->assertFalse($checker->isDisposable('user@yahoo.com'));
        $this->assertFalse($checker->isDisposable('user@hotmail.com'));
        $this->assertFalse($checker->isDisposable('user@outlook.com'));
    }

    public function testCheckReturnsCheckResultForDisposableEmail(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');
        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $result = $checker->check('user@mailinator.com');

        $this->assertInstanceOf(CheckResult::class, $result);
        $this->assertTrue($result->isDisposable());
        $this->assertFalse($result->isSafe());
        $this->assertFalse($result->isWhitelisted());
        $this->assertSame('mailinator.com', $result->getDomain());
        $this->assertSame('user@mailinator.com', $result->getOriginalInput());
        $this->assertNotNull($result->getMatchedChecker());
    }

    public function testCheckReturnsCheckResultForSafeEmail(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');
        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $result = $checker->check('user@gmail.com');

        $this->assertInstanceOf(CheckResult::class, $result);
        $this->assertFalse($result->isDisposable());
        $this->assertTrue($result->isSafe());
        $this->assertFalse($result->isWhitelisted());
        $this->assertSame('gmail.com', $result->getDomain());
    }

    public function testCheckThrowsForInvalidEmail(): void
    {
        $checker = DisposableEmailChecker::create();

        $this->expectException(InvalidDomainException::class);

        $checker->check('notanemail');
    }

    public function testCheckSafeReturnsSafeResultForInvalidEmail(): void
    {
        $checker = DisposableEmailChecker::create();

        $result = $checker->checkSafe('notanemail');

        $this->assertInstanceOf(CheckResult::class, $result);
        $this->assertTrue($result->isSafe());
        $this->assertFalse($result->isDisposable());
        $this->assertSame('', $result->getDomain());
        $this->assertSame('notanemail', $result->getOriginalInput());
    }

    public function testCheckSafeReturnsCorrectResultForValidEmail(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');
        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $disposable = $checker->checkSafe('user@mailinator.com');
        $this->assertTrue($disposable->isDisposable());

        $safe = $checker->checkSafe('user@gmail.com');
        $this->assertTrue($safe->isSafe());
    }

    public function testCheckDomainReturnsCheckResult(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');
        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $disposable = $checker->checkDomain('mailinator.com');
        $this->assertTrue($disposable->isDisposable());
        $this->assertSame('mailinator.com', $disposable->getDomain());

        $safe = $checker->checkDomain('gmail.com');
        $this->assertTrue($safe->isSafe());
    }

    public function testCheckDomainThrowsForInvalidDomain(): void
    {
        $checker = DisposableEmailChecker::create();

        $this->expectException(InvalidDomainException::class);

        $checker->checkDomain('');
    }

    public function testCheckBatchReturnsResultsKeyedByEmail(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');
        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $emails = [
            'user@mailinator.com',
            'user@gmail.com',
            'notanemail',
        ];

        $results = $checker->checkBatch($emails);

        $this->assertCount(3, $results);
        $this->assertArrayHasKey('user@mailinator.com', $results);
        $this->assertArrayHasKey('user@gmail.com', $results);
        $this->assertArrayHasKey('notanemail', $results);

        $this->assertInstanceOf(CheckResult::class, $results['user@mailinator.com']);
        $this->assertTrue($results['user@mailinator.com']->isDisposable());
        $this->assertTrue($results['user@gmail.com']->isSafe());
        $this->assertTrue($results['notanemail']->isSafe());
    }

    public function testIsDisposableBatchReturnsBooleansKeyedByEmail(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');
        $checker = DisposableEmailChecker::create(new FileChecker($this->tempFile));

        $emails = [
            'user@mailinator.com',
            'user@gmail.com',
            'notanemail',
        ];

        $results = $checker->isDisposableBatch($emails);

        $this->assertCount(3, $results);
        $this->assertTrue($results['user@mailinator.com']);
        $this->assertFalse($results['user@gmail.com']);
        $this->assertFalse($results['notanemail']);
    }

    public function testCheckReturnsWhitelistedResultWithCacheAndWhitelist(): void
    {
        $this->tempFile = $this->createTempFile("mailinator.com\nexample.com");

        $checker = DisposableEmailChecker::builder()
            ->withDomainsFile($this->tempFile)
            ->withWhitelist(['example.com'])
            ->withCache(new \Raul3k\DisposableBlocker\Core\Cache\ArrayCache())
            ->build();

        $result = $checker->check('user@example.com');

        $this->assertTrue($result->isWhitelisted());
        $this->assertFalse($result->isDisposable());
        $this->assertTrue($result->isSafe());
    }

    public function testCheckDomainReturnsWhitelistedResultWithCacheAndWhitelist(): void
    {
        $this->tempFile = $this->createTempFile("mailinator.com\nexample.com");

        $checker = DisposableEmailChecker::builder()
            ->withDomainsFile($this->tempFile)
            ->withWhitelist(['example.com'])
            ->withCache(new \Raul3k\DisposableBlocker\Core\Cache\ArrayCache())
            ->build();

        $result = $checker->checkDomain('example.com');

        $this->assertTrue($result->isWhitelisted());
        $this->assertFalse($result->isDisposable());
    }
}
