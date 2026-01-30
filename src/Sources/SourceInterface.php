<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Sources;

use Raul3k\BlockDisposable\Core\Parsers\ParserInterface;

interface SourceInterface
{
    /**
     * Get the unique name/identifier for this source.
     */
    public function getName(): string;

    /**
     * Get the URL of this source (if applicable).
     */
    public function getUrl(): ?string;

    /**
     * Get the parser used by this source.
     */
    public function getParser(): ParserInterface;

    /**
     * Fetch and parse domains from this source.
     *
     * @return iterable<string> List of domain strings
     */
    public function fetch(): iterable;
}
