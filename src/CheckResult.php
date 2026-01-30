<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core;

/**
 * Detailed result from a disposable email check.
 *
 * Contains information about the check result including which
 * checker matched, confidence level, and whitelist status.
 */
final class CheckResult
{
    public const CONFIDENCE_HIGH = 1.0;

    public const CONFIDENCE_MEDIUM = 0.7;

    public const CONFIDENCE_LOW = 0.4;

    public const CONFIDENCE_NONE = 0.0;

    public function __construct(
        private readonly string $domain,
        private readonly string $originalInput,
        private readonly bool $isDisposable,
        private readonly ?string $matchedChecker = null,
        private readonly bool $isWhitelisted = false,
        private readonly float $confidence = self::CONFIDENCE_HIGH
    ) {
    }

    /**
     * Create a result for a disposable domain.
     */
    public static function disposable(
        string $domain,
        string $originalInput,
        string $matchedChecker,
        float $confidence = self::CONFIDENCE_HIGH
    ): self {
        return new self(
            domain: $domain,
            originalInput: $originalInput,
            isDisposable: true,
            matchedChecker: $matchedChecker,
            isWhitelisted: false,
            confidence: $confidence
        );
    }

    /**
     * Create a result for a safe (non-disposable) domain.
     */
    public static function safe(string $domain, string $originalInput): self
    {
        return new self(
            domain: $domain,
            originalInput: $originalInput,
            isDisposable: false,
            matchedChecker: null,
            isWhitelisted: false,
            confidence: self::CONFIDENCE_HIGH
        );
    }

    /**
     * Create a result for a whitelisted domain.
     */
    public static function whitelisted(string $domain, string $originalInput): self
    {
        return new self(
            domain: $domain,
            originalInput: $originalInput,
            isDisposable: false,
            matchedChecker: null,
            isWhitelisted: true,
            confidence: self::CONFIDENCE_HIGH
        );
    }

    /**
     * Get the normalized domain that was checked.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Get the original input (email or domain).
     */
    public function getOriginalInput(): string
    {
        return $this->originalInput;
    }

    /**
     * Check if the domain is disposable.
     */
    public function isDisposable(): bool
    {
        return $this->isDisposable;
    }

    /**
     * Check if the domain is safe (not disposable).
     */
    public function isSafe(): bool
    {
        return !$this->isDisposable;
    }

    /**
     * Get the name of the checker that matched.
     * Returns null if the domain is not disposable.
     */
    public function getMatchedChecker(): ?string
    {
        return $this->matchedChecker;
    }

    /**
     * Check if the domain was whitelisted.
     */
    public function isWhitelisted(): bool
    {
        return $this->isWhitelisted;
    }

    /**
     * Get the confidence level of the result (0.0 to 1.0).
     */
    public function getConfidence(): float
    {
        return $this->confidence;
    }

    /**
     * Check if this is a high-confidence result.
     */
    public function isHighConfidence(): bool
    {
        return $this->confidence >= self::CONFIDENCE_HIGH;
    }

    /**
     * Convert the result to an array.
     *
     * @return array{
     *     domain: string,
     *     original_input: string,
     *     is_disposable: bool,
     *     is_safe: bool,
     *     matched_checker: string|null,
     *     is_whitelisted: bool,
     *     confidence: float
     * }
     */
    public function toArray(): array
    {
        return [
            'domain' => $this->domain,
            'original_input' => $this->originalInput,
            'is_disposable' => $this->isDisposable,
            'is_safe' => $this->isSafe(),
            'matched_checker' => $this->matchedChecker,
            'is_whitelisted' => $this->isWhitelisted,
            'confidence' => $this->confidence,
        ];
    }

    /**
     * Convert the result to JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
