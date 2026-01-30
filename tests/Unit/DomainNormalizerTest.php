<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit;

use Raul3k\BlockDisposable\Core\DomainNormalizer;
use Raul3k\BlockDisposable\Core\Exceptions\InvalidDomainException;
use Raul3k\BlockDisposable\Core\Tests\TestCase;

class DomainNormalizerTest extends TestCase
{
    private DomainNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new DomainNormalizer();
    }

    public function testNormalizeFromEmailExtractsDomain(): void
    {
        $this->assertSame('gmail.com', $this->normalizer->normalizeFromEmail('user@gmail.com'));
        $this->assertSame('example.com', $this->normalizer->normalizeFromEmail('test@example.com'));
    }

    public function testNormalizeFromEmailHandlesSubdomains(): void
    {
        $this->assertSame('gmail.com', $this->normalizer->normalizeFromEmail('user@mail.gmail.com'));
        $this->assertSame('example.com', $this->normalizer->normalizeFromEmail('user@sub.example.com'));
    }

    public function testNormalizeFromEmailLowercasesDomain(): void
    {
        $this->assertSame('gmail.com', $this->normalizer->normalizeFromEmail('user@GMAIL.COM'));
        $this->assertSame('gmail.com', $this->normalizer->normalizeFromEmail('user@Gmail.Com'));
    }

    public function testNormalizeFromEmailTrimsWhitespace(): void
    {
        $this->assertSame('gmail.com', $this->normalizer->normalizeFromEmail('  user@gmail.com  '));
    }

    public function testNormalizeFromEmailThrowsForEmptyInput(): void
    {
        $this->expectException(InvalidDomainException::class);

        $this->normalizer->normalizeFromEmail('');
    }

    public function testNormalizeFromEmailThrowsForMissingAtSign(): void
    {
        $this->expectException(InvalidDomainException::class);

        $this->normalizer->normalizeFromEmail('notanemail');
    }

    public function testNormalizeDomainReturnsRegistrableDomain(): void
    {
        $this->assertSame('example.com', $this->normalizer->normalizeDomain('example.com'));
        $this->assertSame('example.com', $this->normalizer->normalizeDomain('sub.example.com'));
        $this->assertSame('example.com', $this->normalizer->normalizeDomain('deep.sub.example.com'));
    }

    public function testNormalizeDomainLowercases(): void
    {
        $this->assertSame('example.com', $this->normalizer->normalizeDomain('EXAMPLE.COM'));
    }

    public function testNormalizeDomainTrimsWhitespace(): void
    {
        $this->assertSame('example.com', $this->normalizer->normalizeDomain('  example.com  '));
    }

    public function testNormalizeDomainHandlesPublicSuffixes(): void
    {
        // co.uk is a public suffix, so example.co.uk is the registrable domain
        $this->assertSame('example.co.uk', $this->normalizer->normalizeDomain('example.co.uk'));
        $this->assertSame('example.co.uk', $this->normalizer->normalizeDomain('sub.example.co.uk'));
    }

    public function testNormalizeDomainThrowsForEmptyInput(): void
    {
        $this->expectException(InvalidDomainException::class);

        $this->normalizer->normalizeDomain('');
    }

    public function testCanNormalizeReturnsTrueForValidEmail(): void
    {
        $this->assertTrue($this->normalizer->canNormalize('user@example.com'));
    }

    public function testCanNormalizeReturnsTrueForValidDomain(): void
    {
        $this->assertTrue($this->normalizer->canNormalize('example.com'));
    }

    public function testCanNormalizeReturnsFalseForInvalidInput(): void
    {
        $this->assertFalse($this->normalizer->canNormalize(''));
        $this->assertFalse($this->normalizer->canNormalize('notvalid'));
    }

    public function testIsValidEmailFormatReturnsTrueForValidEmail(): void
    {
        $this->assertTrue($this->normalizer->isValidEmailFormat('user@example.com'));
        $this->assertTrue($this->normalizer->isValidEmailFormat('test.user+tag@sub.example.com'));
    }

    public function testIsValidEmailFormatReturnsFalseForInvalidEmail(): void
    {
        $this->assertFalse($this->normalizer->isValidEmailFormat('notanemail'));
        $this->assertFalse($this->normalizer->isValidEmailFormat('@nodomain'));
        $this->assertFalse($this->normalizer->isValidEmailFormat('no@'));
    }

    public function testHandlesInternationalDomains(): void
    {
        // IDN domains should be converted to punycode
        $this->assertSame('xn--e1afmkfd.xn--p1ai', $this->normalizer->normalizeDomain('пример.рф'));
    }

    public function testHandlesEmailWithMultipleAtSigns(): void
    {
        // Should use the last @ sign
        $this->assertSame('example.com', $this->normalizer->normalizeFromEmail('"test@local"@example.com'));
    }
}
