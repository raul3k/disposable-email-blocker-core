<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Parsers;

interface ParserInterface
{
    /**
     * Parse raw content and return an iterable of domains.
     *
     * @param string $content Raw content from source
     * @return iterable<string> List of normalized domain strings
     */
    public function parse(string $content): iterable;

    /**
     * Get the name of this parser.
     */
    public function getName(): string;
}
