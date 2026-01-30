<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Checkers;

/**
 * Decorator that wraps another checker and allows whitelisting specific domains.
 *
 * Whitelisted domains will always return false (not disposable),
 * bypassing the underlying checker entirely.
 */
class WhitelistChecker implements CheckerInterface
{
    /** @var array<string, true> */
    private array $whitelist = [];

    /**
     * @param CheckerInterface $checker The underlying checker to wrap
     * @param array<string> $whitelist Initial list of whitelisted domains
     */
    public function __construct(
        private readonly CheckerInterface $checker,
        array $whitelist = []
    ) {
        foreach ($whitelist as $domain) {
            $this->whitelist[strtolower($domain)] = true;
        }
    }

    public function isDomainDisposable(string $normalizedDomain): bool
    {
        $domain = strtolower($normalizedDomain);

        // Check exact match
        if (isset($this->whitelist[$domain])) {
            return false;
        }

        // Check parent domain (e.g., sub.example.com -> example.com)
        $parts = explode('.', $domain);
        while (count($parts) > 2) {
            array_shift($parts);
            $parentDomain = implode('.', $parts);
            if (isset($this->whitelist[$parentDomain])) {
                return false;
            }
        }

        return $this->checker->isDomainDisposable($normalizedDomain);
    }

    /**
     * Add a domain to the whitelist.
     */
    public function addToWhitelist(string $domain): self
    {
        $this->whitelist[strtolower($domain)] = true;

        return $this;
    }

    /**
     * Remove a domain from the whitelist.
     */
    public function removeFromWhitelist(string $domain): self
    {
        unset($this->whitelist[strtolower($domain)]);

        return $this;
    }

    /**
     * Check if a domain is whitelisted.
     */
    public function isWhitelisted(string $domain): bool
    {
        $domain = strtolower($domain);

        if (isset($this->whitelist[$domain])) {
            return true;
        }

        // Check parent domains
        $parts = explode('.', $domain);
        while (count($parts) > 2) {
            array_shift($parts);
            $parentDomain = implode('.', $parts);
            if (isset($this->whitelist[$parentDomain])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all whitelisted domains.
     *
     * @return array<string>
     */
    public function getWhitelist(): array
    {
        return array_keys($this->whitelist);
    }

    /**
     * Clear all whitelisted domains.
     */
    public function clearWhitelist(): self
    {
        $this->whitelist = [];

        return $this;
    }

    /**
     * Get the underlying checker.
     */
    public function getWrappedChecker(): CheckerInterface
    {
        return $this->checker;
    }
}
