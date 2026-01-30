<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Checkers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Raul3k\BlockDisposable\Core\Checkers\PatternChecker;

class PatternCheckerTest extends TestCase
{
    private PatternChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new PatternChecker();
    }

    #[DataProvider('disposablePatternsProvider')]
    public function testDetectsDisposablePatterns(string $domain): void
    {
        $this->assertTrue(
            $this->checker->isDomainDisposable($domain),
            "Expected '$domain' to be detected as disposable"
        );
    }

    public static function disposablePatternsProvider(): array
    {
        return [
            'tempmail prefix' => ['tempmail.com'],
            'temp prefix' => ['temp.com'],
            'tempmail suffix' => ['my-tempmail.com'],
            'disposable prefix' => ['disposable.net'],
            'throwaway prefix' => ['throwaway.io'],
            'trashmail' => ['trashmail.com'],
            'junkmail' => ['junkmail.org'],
            'fakemail' => ['fakemail.net'],
            'fakeinbox' => ['fakeinbox.com'],
            'spammail' => ['spammail.me'],
            '10minutemail' => ['10minutemail.com'],
            '5minmail' => ['5minmail.net'],
            'anonymous mail' => ['anonymousmail.org'],
            'anonmail' => ['anonmail.com'],
            'burnermail' => ['burnermail.io'],
            'guerrillamail' => ['guerrillamail.com'],
            'guerrilla variation' => ['guerrilla.mail'],
            'yopmail' => ['yopmail.com'],
            'mailinator' => ['mailinator.com'],
            'maildrop' => ['maildrop.cc'],
            'getairmail' => ['getairmail.com'],
            '20minutemail' => ['20minutemail.com'],
            'catchall' => ['catchall.net'],
            '.tk TLD' => ['something.tk'],
            '.ml TLD' => ['domain.ml'],
            '.ga TLD' => ['test.ga'],
            '.cf TLD' => ['mail.cf'],
            '.gq TLD' => ['free.gq'],
        ];
    }

    #[DataProvider('legitimateDomainsProvider')]
    public function testDoesNotFlagLegitimateDomains(string $domain): void
    {
        $this->assertFalse(
            $this->checker->isDomainDisposable($domain),
            "Expected '$domain' to NOT be detected as disposable"
        );
    }

    public static function legitimateDomainsProvider(): array
    {
        return [
            'gmail' => ['gmail.com'],
            'outlook' => ['outlook.com'],
            'yahoo' => ['yahoo.com'],
            'hotmail' => ['hotmail.com'],
            'protonmail' => ['protonmail.com'],
            'icloud' => ['icloud.com'],
            'corporate' => ['company.com'],
            'example' => ['example.org'],
            'university' => ['university.edu'],
        ];
    }

    public function testCustomPatterns(): void
    {
        $checker = new PatternChecker(['/^custom-pattern/i']);

        $this->assertTrue($checker->isDomainDisposable('custom-pattern.com'));
        $this->assertFalse($checker->isDomainDisposable('tempmail.com'));
    }

    public function testAddPattern(): void
    {
        $this->assertFalse($this->checker->isDomainDisposable('myspecial.com'));

        $this->checker->addPattern('/^myspecial/i');

        $this->assertTrue($this->checker->isDomainDisposable('myspecial.com'));
    }

    public function testGetPatterns(): void
    {
        $patterns = $this->checker->getPatterns();

        $this->assertIsArray($patterns);
        $this->assertNotEmpty($patterns);
    }

    public function testCachingWorks(): void
    {
        // First call
        $result1 = $this->checker->isDomainDisposable('tempmail.com');
        // Second call should use cache
        $result2 = $this->checker->isDomainDisposable('tempmail.com');

        $this->assertTrue($result1);
        $this->assertTrue($result2);
    }

    public function testClearCache(): void
    {
        $this->checker->isDomainDisposable('tempmail.com');
        $this->checker->clearCache();

        // Should still work after clearing cache
        $this->assertTrue($this->checker->isDomainDisposable('tempmail.com'));
    }
}
