<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit\Parsers;

use Raul3k\BlockDisposable\Core\Parsers\JsonArrayParser;
use Raul3k\BlockDisposable\Core\Tests\TestCase;
use RuntimeException;

class JsonArrayParserTest extends TestCase
{
    public function testParseFlatArray(): void
    {
        $parser = new JsonArrayParser();

        $domains = iterator_to_array($parser->parse('["mailinator.com", "guerrillamail.com"]'));

        $this->assertSame(['mailinator.com', 'guerrillamail.com'], $domains);
    }

    public function testParseLowercasesDomains(): void
    {
        $parser = new JsonArrayParser();

        $domains = iterator_to_array($parser->parse('["MAILINATOR.COM", "GuerrillaMAIL.com"]'));

        $this->assertSame(['mailinator.com', 'guerrillamail.com'], $domains);
    }

    public function testParseTrimsWhitespace(): void
    {
        $parser = new JsonArrayParser();

        $domains = iterator_to_array($parser->parse('["  mailinator.com  ", "\tguerrillamail.com\t"]'));

        $this->assertSame(['mailinator.com', 'guerrillamail.com'], $domains);
    }

    public function testParseWithJsonPath(): void
    {
        $parser = new JsonArrayParser('data.domains');

        $json = '{"data": {"domains": ["mailinator.com", "temp-mail.org"]}}';
        $domains = iterator_to_array($parser->parse($json));

        $this->assertSame(['mailinator.com', 'temp-mail.org'], $domains);
    }

    public function testParseIgnoresEmptyStrings(): void
    {
        $parser = new JsonArrayParser();

        $domains = iterator_to_array($parser->parse('["mailinator.com", "", "  ", "guerrillamail.com"]'));

        $this->assertSame(['mailinator.com', 'guerrillamail.com'], $domains);
    }

    public function testParseIgnoresNonStringValues(): void
    {
        $parser = new JsonArrayParser();

        $domains = iterator_to_array($parser->parse('["mailinator.com", 123, null, "guerrillamail.com"]'));

        $this->assertSame(['mailinator.com', 'guerrillamail.com'], $domains);
    }

    public function testGetName(): void
    {
        $parser = new JsonArrayParser();

        $this->assertSame('json_array', $parser->getName());
    }

    public function testThrowsExceptionForInvalidJson(): void
    {
        $parser = new JsonArrayParser();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON');

        iterator_to_array($parser->parse('not valid json'));
    }

    public function testThrowsExceptionForNonArrayJson(): void
    {
        $parser = new JsonArrayParser();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JSON must be an array');

        iterator_to_array($parser->parse('{"key": "value"}'));
    }

    public function testThrowsExceptionForInvalidJsonPath(): void
    {
        $parser = new JsonArrayParser('invalid.path');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JSON path "invalid.path" not found');

        iterator_to_array($parser->parse('{"data": {}}'));
    }

    public function testThrowsExceptionWhenPathDoesNotPointToArray(): void
    {
        $parser = new JsonArrayParser('data.value');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not point to an array');

        iterator_to_array($parser->parse('{"data": {"value": "string"}}'));
    }
}
