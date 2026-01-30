<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Parsers;

use Closure;

/**
 * Parser that uses a custom callback for parsing.
 * Allows users to define their own parsing logic for custom formats.
 */
class CallbackParser implements ParserInterface
{
    /**
     * @param Closure(string): iterable<string> $callback
     */
    public function __construct(
        private readonly Closure $callback,
        private readonly string $name = 'callback'
    ) {
    }

    public function parse(string $content): iterable
    {
        return ($this->callback)($content);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
