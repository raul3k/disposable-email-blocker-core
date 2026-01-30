<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Exceptions;

use InvalidArgumentException;

class InvalidDomainException extends InvalidArgumentException
{
    public static function forEmail(string $email): self
    {
        return new self(
            sprintf('Cannot extract valid domain from email: %s', $email)
        );
    }

    public static function forDomain(string $domain): self
    {
        return new self(
            sprintf('Invalid domain: %s', $domain)
        );
    }

    public static function emptyInput(): self
    {
        return new self('Email or domain cannot be empty');
    }
}
