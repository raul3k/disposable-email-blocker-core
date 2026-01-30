<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Checkers;

interface CheckerInterface
{
    /**
     * Check if the given normalized domain is disposable.
     *
     * @param string $normalizedDomain The domain to check (already normalized)
     * @return bool True if the domain is disposable, false otherwise
     */
    public function isDomainDisposable(string $normalizedDomain): bool;
}
