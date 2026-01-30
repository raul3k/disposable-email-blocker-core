<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Checkers;

/**
 * Checker that detects disposable email patterns using regex.
 *
 * This checker identifies suspicious domain name patterns commonly
 * used by disposable/temporary email services.
 */
class PatternChecker implements CheckerInterface
{
    /** @var array<string> */
    private array $patterns;

    /** @var array<string, bool> */
    private array $cache = [];

    /**
     * @param array<string>|null $patterns Custom patterns (null = use defaults)
     */
    public function __construct(?array $patterns = null)
    {
        $this->patterns = $patterns ?? $this->getDefaultPatterns();
    }

    public function isDomainDisposable(string $normalizedDomain): bool
    {
        if (isset($this->cache[$normalizedDomain])) {
            return $this->cache[$normalizedDomain];
        }

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $normalizedDomain) === 1) {
                $this->cache[$normalizedDomain] = true;

                return true;
            }
        }

        $this->cache[$normalizedDomain] = false;

        return false;
    }

    /**
     * Get the patterns being used.
     *
     * @return array<string>
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * Add a custom pattern.
     */
    public function addPattern(string $pattern): self
    {
        $this->patterns[] = $pattern;
        $this->cache = []; // Clear cache when patterns change

        return $this;
    }

    /**
     * Clear the internal cache.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Get the default patterns that match common disposable email naming conventions.
     *
     * @return array<string>
     */
    private function getDefaultPatterns(): array
    {
        return [
            // Temporary/disposable keywords
            '/^temp(?:mail|email|inbox)?[.-]?/i',
            '/[.-]?temp(?:mail|email|inbox)?$/i',
            '/^disposable[.-]?/i',
            '/[.-]?disposable$/i',
            '/^throwaway[.-]?/i',
            '/[.-]?throwaway$/i',
            '/^trash[.-]?(?:mail|email)?/i',
            '/^junk[.-]?(?:mail|email)?/i',
            '/^fake[.-]?(?:mail|email|inbox)?/i',
            '/^spam[.-]?(?:mail|email)?/i',

            // Time-based patterns (10minutemail, 5min, etc)
            '/^\d+min(?:ute)?(?:s)?(?:mail|email)?/i',
            '/^\d+hour(?:s)?(?:mail|email)?/i',

            // Anonymous/burner patterns
            '/^(?:anon|anonymous)[.-]?(?:mail|email|box)?/i',
            '/^burner[.-]?(?:mail|email)?/i',
            '/^guerr?illa[.-]?(?:mail)?/i',
            '/^yopmail/i',
            '/^mailinator/i',
            '/^maildrop/i',
            '/^getairmail/i',
            '/^mohmal/i',
            '/^tempail/i',
            '/^emailondeck/i',
            '/^(?:10|20)minute(?:s)?(?:mail)?/i',

            // Catch-all inbox patterns
            '/^catch[.-]?all/i',
            '/^mailcatch/i',
            '/^inboxalias/i',

            // Random-looking patterns (x23mail, mail123)
            '/^[a-z]{1,3}\d{2,}mail/i',
            '/^mail\d{3,}/i',

            // Common disposable TLD patterns
            '/\.tk$/i',
            '/\.ml$/i',
            '/\.ga$/i',
            '/\.cf$/i',
            '/\.gq$/i',
        ];
    }
}
