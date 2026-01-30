<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit\Parsers;

use Raul3k\BlockDisposable\Core\Parsers\TextLineParser;
use Raul3k\BlockDisposable\Core\Tests\TestCase;

class TextLineParserTest extends TestCase
{
    public function testParseReturnsDomains(): void
    {
        $parser = new TextLineParser();

        $domains = iterator_to_array($parser->parse("mailinator.com\nguerrillamail.com\ntemp-mail.org"));

        $this->assertSame(['mailinator.com', 'guerrillamail.com', 'temp-mail.org'], $domains);
    }

    public function testParseIgnoresEmptyLines(): void
    {
        $parser = new TextLineParser();

        $domains = iterator_to_array($parser->parse("mailinator.com\n\n\nguerrillamail.com"));

        $this->assertSame(['mailinator.com', 'guerrillamail.com'], $domains);
    }

    public function testParseIgnoresCommentLines(): void
    {
        $parser = new TextLineParser();

        $domains = iterator_to_array($parser->parse("# This is a comment\nmailinator.com\n# Another comment"));

        $this->assertSame(['mailinator.com'], $domains);
    }

    public function testParseLowercasesDomains(): void
    {
        $parser = new TextLineParser();

        $domains = iterator_to_array($parser->parse("MAILINATOR.COM\nGuerrillamail.Com"));

        $this->assertSame(['mailinator.com', 'guerrillamail.com'], $domains);
    }

    public function testParseTrimsWhitespace(): void
    {
        $parser = new TextLineParser();

        $domains = iterator_to_array($parser->parse("  mailinator.com  \n\tguerrillamail.com\t"));

        $this->assertSame(['mailinator.com', 'guerrillamail.com'], $domains);
    }

    public function testGetName(): void
    {
        $parser = new TextLineParser();

        $this->assertSame('text_line', $parser->getName());
    }

    public function testParseHandlesWindowsLineEndings(): void
    {
        $parser = new TextLineParser();

        $domains = iterator_to_array($parser->parse("mailinator.com\r\nguerrillamail.com\r\n"));

        $this->assertSame(['mailinator.com', 'guerrillamail.com'], $domains);
    }

    public function testParseReturnsGenerator(): void
    {
        $parser = new TextLineParser();

        $result = $parser->parse('mailinator.com');

        $this->assertInstanceOf(\Generator::class, $result);
    }
}
