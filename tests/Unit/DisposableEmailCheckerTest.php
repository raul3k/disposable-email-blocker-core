<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit;

use Raul3k\BlockDisposable\Core\Checkers\CallbackChecker;
use Raul3k\BlockDisposable\Core\Checkers\FileChecker;
use Raul3k\BlockDisposable\Core\DisposableEmailChecker;
use Raul3k\BlockDisposable\Core\DomainNormalizer;
use Raul3k\BlockDisposable\Core\Exceptions\InvalidDomainException;
use Raul3k\BlockDisposable\Core\Tests\TestCase;

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
}
