<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Checkers;

use Closure;

class CallbackChecker implements CheckerInterface
{
    /**
     * @param Closure(string): bool $callback
     */
    public function __construct(
        private readonly Closure $callback
    ) {
    }

    public function isDomainDisposable(string $normalizedDomain): bool
    {
        return ($this->callback)($normalizedDomain);
    }
}
