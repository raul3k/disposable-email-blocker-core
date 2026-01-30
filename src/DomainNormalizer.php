<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core;

use Pdp\Domain;
use Pdp\Rules;
use Raul3k\BlockDisposable\Core\Exceptions\InvalidDomainException;

class DomainNormalizer
{
    private ?Rules $rules = null;

    private readonly string $pslPath;

    public function __construct(?string $pslPath = null)
    {
        $this->pslPath = $pslPath ?? $this->getDefaultPslPath();
    }

    /**
     * Normalize a domain from an email address.
     *
     * @throws InvalidDomainException
     */
    public function normalizeFromEmail(string $email): string
    {
        $email = trim($email);

        if ($email === '') {
            throw InvalidDomainException::emptyInput();
        }

        $atPosition = strrpos($email, '@');
        if ($atPosition === false) {
            throw InvalidDomainException::forEmail($email);
        }

        $domain = substr($email, $atPosition + 1);

        return $this->normalizeDomain($domain);
    }

    /**
     * Normalize a domain string.
     *
     * @throws InvalidDomainException
     */
    public function normalizeDomain(string $domain): string
    {
        $domain = trim($domain);

        if ($domain === '') {
            throw InvalidDomainException::emptyInput();
        }

        // Lowercase
        $domain = strtolower($domain);

        // Convert to ASCII (Punycode) if needed
        $domain = $this->toAscii($domain);

        // Extract registrable domain using PSL
        $registrableDomain = $this->extractRegistrableDomain($domain);

        if ($registrableDomain === null) {
            throw InvalidDomainException::forDomain($domain);
        }

        return $registrableDomain;
    }

    /**
     * Check if a string can be normalized to a valid domain.
     */
    public function canNormalize(string $emailOrDomain): bool
    {
        try {
            if (str_contains($emailOrDomain, '@')) {
                $this->normalizeFromEmail($emailOrDomain);
            } else {
                $this->normalizeDomain($emailOrDomain);
            }

            return true;
        } catch (InvalidDomainException) {
            return false;
        }
    }

    /**
     * Check if the input looks like a valid email format.
     */
    public function isValidEmailFormat(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function toAscii(string $domain): string
    {
        if (!preg_match('/[^\x20-\x7f]/', $domain)) {
            return $domain;
        }

        $ascii = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        return $ascii !== false ? $ascii : $domain;
    }

    private function extractRegistrableDomain(string $domain): ?string
    {
        $rules = $this->getRules();

        try {
            $result = $rules->resolve(Domain::fromIDNA2008($domain));
            $registrable = $result->registrableDomain();

            if ($registrable->toString() === '') {
                return null;
            }

            return $registrable->toString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function getRules(): Rules
    {
        if ($this->rules === null) {
            if (!is_file($this->pslPath) || !is_readable($this->pslPath)) {
                throw new \RuntimeException(
                    sprintf('Cannot read PSL file: %s', $this->pslPath)
                );
            }

            $this->rules = Rules::fromPath($this->pslPath);
        }

        return $this->rules;
    }

    private function getDefaultPslPath(): string
    {
        return __DIR__ . '/Resources/public_suffix_list.dat';
    }
}
