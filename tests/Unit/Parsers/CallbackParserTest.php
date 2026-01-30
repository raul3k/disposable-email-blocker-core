<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit\Parsers;

use Raul3k\BlockDisposable\Core\Parsers\CallbackParser;
use Raul3k\BlockDisposable\Core\Tests\TestCase;

class CallbackParserTest extends TestCase
{
    public function testParseCallsCallback(): void
    {
        $parser = new CallbackParser(function (string $content): iterable {
            foreach (explode(',', $content) as $item) {
                yield $item;
            }
        });

        $domains = iterator_to_array($parser->parse('mailinator.com,guerrillamail.com'));

        $this->assertSame(['mailinator.com', 'guerrillamail.com'], $domains);
    }

    public function testParsePassesContentToCallback(): void
    {
        $receivedContent = null;

        $parser = new CallbackParser(function (string $content) use (&$receivedContent): iterable {
            $receivedContent = $content;

            return [];
        });

        $parser->parse('test content');

        $this->assertSame('test content', $receivedContent);
    }

    public function testGetNameReturnsDefaultName(): void
    {
        $parser = new CallbackParser(fn ($c) => []);

        $this->assertSame('callback', $parser->getName());
    }

    public function testGetNameReturnsCustomName(): void
    {
        $parser = new CallbackParser(fn ($c) => [], 'custom-parser');

        $this->assertSame('custom-parser', $parser->getName());
    }

    public function testCanParseCustomFormat(): void
    {
        // Example: CSV format with domain in second column
        $parser = new CallbackParser(function (string $content): iterable {
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $parts = str_getcsv($line);
                if (isset($parts[1])) {
                    yield strtolower(trim($parts[1]));
                }
            }
        }, 'csv-parser');

        $csv = "1,mailinator.com,active\n2,temp-mail.org,active";
        $domains = iterator_to_array($parser->parse($csv));

        $this->assertSame(['mailinator.com', 'temp-mail.org'], $domains);
    }

    public function testCallbackCanReturnGenerator(): void
    {
        $parser = new CallbackParser(function (string $content): iterable {
            foreach (explode(',', $content) as $domain) {
                yield trim($domain);
            }
        });

        $domains = iterator_to_array($parser->parse('a.com, b.com, c.com'));

        $this->assertSame(['a.com', 'b.com', 'c.com'], $domains);
    }
}
