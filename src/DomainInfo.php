<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core;

/**
 * Parsed domain information.
 *
 * @example
 * $info = DomainInfo::parse('user@mail.example.co.uk');
 * $info->domain();           // 'example.co.uk'
 * $info->subdomain();        // 'mail'
 * $info->publicSuffix();     // 'co.uk'
 * $info->secondLevelDomain(); // 'example'
 * $info->isIcann();          // true
 * $info->isPrivate();        // false
 */
final class DomainInfo
{
    private function __construct(
        private readonly string $originalInput,
        private readonly ?string $fullHost,
        private readonly ?string $registrableDomain,
        private readonly ?string $subdomain,
        private readonly ?string $publicSuffix,
        private readonly ?string $secondLevelDomain,
        private readonly bool $isIcann,
        private readonly bool $isPrivate,
        private readonly bool $isKnownSuffix,
        private readonly ?string $ascii,
        private readonly ?string $unicode,
    ) {
    }

    /**
     * Parse a domain or email address.
     */
    public static function parse(string $input): self
    {
        $normalizer = new DomainNormalizer();

        return self::parseWithNormalizer($input, $normalizer);
    }

    /**
     * Parse using an existing normalizer instance (for performance).
     */
    public static function parseWithNormalizer(string $input, DomainNormalizer $normalizer): self
    {
        $host = self::extractHost($input);

        if ($host === null || $host === '') {
            return self::empty($input);
        }

        $result = $normalizer->getResolveResult($host);

        if ($result === null) {
            return self::empty($input);
        }

        $suffix = $result->suffix();
        $registrable = $result->registrableDomain();
        $subdomain = $result->subDomain()->toString();

        // Extract second level domain (the part before the public suffix)
        $secondLevel = null;
        if ($registrable->toString() !== '' && $suffix->toString() !== '') {
            $regStr = $registrable->toString();
            $sufStr = $suffix->toString();
            $secondLevel = rtrim(substr($regStr, 0, -strlen($sufStr)), '.');
        }

        return new self(
            originalInput: $input,
            fullHost: $host,
            registrableDomain: $registrable->toString() ?: null,
            subdomain: $subdomain !== '' ? $subdomain : null,
            publicSuffix: $suffix->toString() ?: null,
            secondLevelDomain: $secondLevel,
            isIcann: $suffix->isICANN(),
            isPrivate: $suffix->isPrivate(),
            isKnownSuffix: $suffix->isKnown(),
            ascii: $result->toAscii()->toString() ?: null,
            unicode: $result->toUnicode()->toString() ?: null,
        );
    }

    /**
     * Create an empty result for invalid input.
     */
    private static function empty(string $input): self
    {
        return new self(
            originalInput: $input,
            fullHost: null,
            registrableDomain: null,
            subdomain: null,
            publicSuffix: null,
            secondLevelDomain: null,
            isIcann: false,
            isPrivate: false,
            isKnownSuffix: false,
            ascii: null,
            unicode: null,
        );
    }

    /**
     * Extract hostname from email or domain string.
     */
    private static function extractHost(string $input): ?string
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        // If it looks like an email, extract domain part
        if (str_contains($input, '@')) {
            $parts = explode('@', $input);

            return strtolower(trim(end($parts)));
        }

        // Remove protocol if present
        $input = (string) preg_replace('#^https?://#i', '', $input);

        // Remove path
        $input = explode('/', $input)[0];

        // Remove port
        $input = explode(':', $input)[0];

        return strtolower(trim($input));
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    /**
     * Get the original input string.
     */
    public function originalInput(): string
    {
        return $this->originalInput;
    }

    /**
     * Get the full hostname (without protocol/path).
     * Example: "mail.example.co.uk"
     */
    public function host(): ?string
    {
        return $this->fullHost;
    }

    /**
     * Get the registrable domain (eTLD+1).
     * Example: "example.co.uk" from "mail.example.co.uk"
     */
    public function domain(): ?string
    {
        return $this->registrableDomain;
    }

    /**
     * Alias for domain().
     */
    public function registrableDomain(): ?string
    {
        return $this->registrableDomain;
    }

    /**
     * Get the subdomain part.
     * Example: "mail" from "mail.example.co.uk"
     */
    public function subdomain(): ?string
    {
        return $this->subdomain;
    }

    /**
     * Get the public suffix (eTLD).
     * Example: "co.uk" from "mail.example.co.uk"
     */
    public function publicSuffix(): ?string
    {
        return $this->publicSuffix;
    }

    /**
     * Alias for publicSuffix().
     */
    public function tld(): ?string
    {
        return $this->publicSuffix;
    }

    /**
     * Get the second level domain (the part before the public suffix).
     * Example: "example" from "mail.example.co.uk"
     */
    public function secondLevelDomain(): ?string
    {
        return $this->secondLevelDomain;
    }

    /**
     * Alias for secondLevelDomain().
     */
    public function sld(): ?string
    {
        return $this->secondLevelDomain;
    }

    /**
     * Check if the public suffix is managed by ICANN.
     * ICANN suffixes are official TLDs like .com, .org, .co.uk
     */
    public function isIcann(): bool
    {
        return $this->isIcann;
    }

    /**
     * Check if the public suffix is private.
     * Private suffixes are managed by private organizations,
     * like .github.io, .s3.amazonaws.com, .blogspot.com
     */
    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    /**
     * Check if the public suffix is in the PSL (either ICANN or private).
     */
    public function isKnownSuffix(): bool
    {
        return $this->isKnownSuffix;
    }

    /**
     * Check if parsing was successful.
     */
    public function isValid(): bool
    {
        return $this->registrableDomain !== null;
    }

    /**
     * Get the ASCII/punycode representation.
     * Example: "xn--e1afmkfd.xn--p1ai" for "пример.рф"
     */
    public function ascii(): ?string
    {
        return $this->ascii;
    }

    /**
     * Get the Unicode representation.
     * Example: "пример.рф"
     */
    public function unicode(): ?string
    {
        return $this->unicode;
    }

    /**
     * Check if the domain uses international characters (IDN).
     */
    public function isIdn(): bool
    {
        return $this->ascii !== null
            && $this->unicode !== null
            && $this->ascii !== $this->unicode;
    }

    /**
     * Convert to array.
     *
     * @return array{
     *     original_input: string,
     *     host: string|null,
     *     domain: string|null,
     *     subdomain: string|null,
     *     public_suffix: string|null,
     *     second_level_domain: string|null,
     *     is_icann: bool,
     *     is_private: bool,
     *     is_known_suffix: bool,
     *     is_valid: bool,
     *     ascii: string|null,
     *     unicode: string|null,
     *     is_idn: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'original_input' => $this->originalInput,
            'host' => $this->fullHost,
            'domain' => $this->registrableDomain,
            'subdomain' => $this->subdomain,
            'public_suffix' => $this->publicSuffix,
            'second_level_domain' => $this->secondLevelDomain,
            'is_icann' => $this->isIcann,
            'is_private' => $this->isPrivate,
            'is_known_suffix' => $this->isKnownSuffix,
            'is_valid' => $this->isValid(),
            'ascii' => $this->ascii,
            'unicode' => $this->unicode,
            'is_idn' => $this->isIdn(),
        ];
    }

    /**
     * Convert to JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
