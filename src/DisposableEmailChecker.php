<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core;

use Raul3k\BlockDisposable\Core\Checkers\ChainChecker;
use Raul3k\BlockDisposable\Core\Checkers\CheckerInterface;
use Raul3k\BlockDisposable\Core\Checkers\FileChecker;
use Raul3k\BlockDisposable\Core\Checkers\WhitelistChecker;
use Raul3k\BlockDisposable\Core\Exceptions\InvalidDomainException;

class DisposableEmailChecker
{
    public function __construct(
        private readonly CheckerInterface $checker,
        private readonly DomainNormalizer $normalizer
    ) {
    }

    /**
     * Create a new instance with default configuration.
     * Uses the bundled disposable domains list.
     */
    public static function create(?CheckerInterface $checker = null): self
    {
        $normalizer = new DomainNormalizer();

        if ($checker === null) {
            $checker = new FileChecker(
                __DIR__ . '/Resources/disposable_domains.txt'
            );
        }

        return new self($checker, $normalizer);
    }

    /**
     * Create a new instance with a custom domain normalizer.
     */
    public static function createWithNormalizer(
        DomainNormalizer $normalizer,
        ?CheckerInterface $checker = null
    ): self {
        if ($checker === null) {
            $checker = new FileChecker(
                __DIR__ . '/Resources/disposable_domains.txt'
            );
        }

        return new self($checker, $normalizer);
    }

    /**
     * Get a fluent builder for creating a checker with custom configuration.
     *
     * @example
     * $checker = DisposableEmailChecker::builder()
     *     ->withBundledDomains()
     *     ->withPatternDetection()
     *     ->withWhitelist(['mycompany.com'])
     *     ->withFileCache('/tmp/cache')
     *     ->build();
     */
    public static function builder(): DisposableEmailCheckerBuilder
    {
        return new DisposableEmailCheckerBuilder();
    }

    /**
     * Check if an email address uses a disposable domain.
     *
     * @throws InvalidDomainException If the email is invalid
     */
    public function isDisposable(string $email): bool
    {
        $domain = $this->normalizer->normalizeFromEmail($email);

        return $this->checker->isDomainDisposable($domain);
    }

    /**
     * Check if an email address uses a disposable domain.
     * Returns false instead of throwing for invalid emails.
     */
    public function isDisposableSafe(string $email): bool
    {
        try {
            return $this->isDisposable($email);
        } catch (InvalidDomainException) {
            return false;
        }
    }

    /**
     * Check if a domain is disposable.
     *
     * @throws InvalidDomainException If the domain is invalid
     */
    public function isDomainDisposable(string $domain): bool
    {
        $normalizedDomain = $this->normalizer->normalizeDomain($domain);

        return $this->checker->isDomainDisposable($normalizedDomain);
    }

    /**
     * Check if a domain is disposable.
     * Returns false instead of throwing for invalid domains.
     */
    public function isDomainDisposableSafe(string $domain): bool
    {
        try {
            return $this->isDomainDisposable($domain);
        } catch (InvalidDomainException) {
            return false;
        }
    }

    /**
     * Normalize an email to its registrable domain.
     *
     * @throws InvalidDomainException If the email is invalid
     */
    public function normalize(string $email): string
    {
        return $this->normalizer->normalizeFromEmail($email);
    }

    /**
     * Get the checker instance.
     */
    public function getChecker(): CheckerInterface
    {
        return $this->checker;
    }

    /**
     * Get the normalizer instance.
     */
    public function getNormalizer(): DomainNormalizer
    {
        return $this->normalizer;
    }

    /**
     * Check an email and return detailed result.
     *
     * @throws InvalidDomainException If the email is invalid
     */
    public function check(string $email): CheckResult
    {
        $domain = $this->normalizer->normalizeFromEmail($email);

        // Check for whitelist first
        if ($this->checker instanceof WhitelistChecker && $this->checker->isWhitelisted($domain)) {
            return CheckResult::whitelisted($domain, $email);
        }

        $isDisposable = $this->checker->isDomainDisposable($domain);

        if (!$isDisposable) {
            return CheckResult::safe($domain, $email);
        }

        // Try to get the matched checker name
        $matchedChecker = $this->getMatchedCheckerName();

        return CheckResult::disposable($domain, $email, $matchedChecker);
    }

    /**
     * Check an email and return detailed result.
     * Returns a safe result instead of throwing for invalid emails.
     */
    public function checkSafe(string $email): CheckResult
    {
        try {
            return $this->check($email);
        } catch (InvalidDomainException) {
            return CheckResult::safe('', $email);
        }
    }

    /**
     * Check a domain and return detailed result.
     *
     * @throws InvalidDomainException If the domain is invalid
     */
    public function checkDomain(string $domain): CheckResult
    {
        $normalizedDomain = $this->normalizer->normalizeDomain($domain);

        // Check for whitelist first
        if ($this->checker instanceof WhitelistChecker && $this->checker->isWhitelisted($normalizedDomain)) {
            return CheckResult::whitelisted($normalizedDomain, $domain);
        }

        $isDisposable = $this->checker->isDomainDisposable($normalizedDomain);

        if (!$isDisposable) {
            return CheckResult::safe($normalizedDomain, $domain);
        }

        $matchedChecker = $this->getMatchedCheckerName();

        return CheckResult::disposable($normalizedDomain, $domain, $matchedChecker);
    }

    /**
     * Check multiple emails at once.
     *
     * @param array<string> $emails
     * @return array<string, CheckResult> Keyed by original email
     */
    public function checkBatch(array $emails): array
    {
        $results = [];

        foreach ($emails as $email) {
            $results[$email] = $this->checkSafe($email);
        }

        return $results;
    }

    /**
     * Check multiple emails and return disposable status.
     *
     * @param array<string> $emails
     * @return array<string, bool> Keyed by original email
     */
    public function isDisposableBatch(array $emails): array
    {
        $results = [];

        foreach ($emails as $email) {
            $results[$email] = $this->isDisposableSafe($email);
        }

        return $results;
    }

    /**
     * Get the name of the checker that matched (if available).
     */
    private function getMatchedCheckerName(): string
    {
        if ($this->checker instanceof ChainChecker) {
            return $this->checker->getLastMatchedChecker() ?? $this->checker::class;
        }

        return $this->checker::class;
    }
}
