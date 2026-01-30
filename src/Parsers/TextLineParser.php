<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Parsers;

/**
 * Parser for plain text files with one domain per line.
 * Ignores empty lines and lines starting with #.
 */
class TextLineParser implements ParserInterface
{
    public function parse(string $content): iterable
    {
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $domain = trim($line);

            if ($domain === '' || str_starts_with($domain, '#')) {
                continue;
            }

            yield strtolower($domain);
        }
    }

    public function getName(): string
    {
        return 'text_line';
    }
}
