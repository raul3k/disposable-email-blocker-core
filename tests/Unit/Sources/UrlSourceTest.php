<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit\Sources;

use Raul3k\BlockDisposable\Core\Parsers\JsonArrayParser;
use Raul3k\BlockDisposable\Core\Parsers\TextLineParser;
use Raul3k\BlockDisposable\Core\Sources\UrlSource;
use Raul3k\BlockDisposable\Core\Tests\TestCase;

class UrlSourceTest extends TestCase
{
    public function testGetNameReturnsProvidedName(): void
    {
        $source = new UrlSource('https://example.com/domains.txt', 'my-source');

        $this->assertSame('my-source', $source->getName());
    }

    public function testGetUrlReturnsProvidedUrl(): void
    {
        $source = new UrlSource('https://example.com/domains.txt', 'my-source');

        $this->assertSame('https://example.com/domains.txt', $source->getUrl());
    }

    public function testGetParserReturnsDefaultTextLineParser(): void
    {
        $source = new UrlSource('https://example.com/domains.txt', 'test');

        $this->assertInstanceOf(TextLineParser::class, $source->getParser());
    }

    public function testGetParserReturnsCustomParser(): void
    {
        $parser = new JsonArrayParser();

        $source = new UrlSource('https://example.com/domains.json', 'test', $parser);

        $this->assertSame($parser, $source->getParser());
    }

    // Note: Actual fetch tests would require mocking or integration tests
    // as they make HTTP requests. Here we just test the configuration.
}
