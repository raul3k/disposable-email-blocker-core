<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit\Sources;

use Raul3k\BlockDisposable\Core\Parsers\JsonArrayParser;
use Raul3k\BlockDisposable\Core\Parsers\TextLineParser;
use Raul3k\BlockDisposable\Core\Sources\FileSource;
use Raul3k\BlockDisposable\Core\Tests\TestCase;
use RuntimeException;

class FileSourceTest extends TestCase
{
    private ?string $tempFile = null;

    protected function tearDown(): void
    {
        if ($this->tempFile !== null) {
            $this->removeTempFile($this->tempFile);
            $this->tempFile = null;
        }
    }

    public function testGetNameReturnsProvidedName(): void
    {
        $this->tempFile = $this->createTempFile('');

        $source = new FileSource($this->tempFile, 'my-source');

        $this->assertSame('my-source', $source->getName());
    }

    public function testGetNameReturnsDefaultName(): void
    {
        $this->tempFile = $this->createTempFile('');

        $source = new FileSource($this->tempFile);

        $this->assertSame('file', $source->getName());
    }

    public function testGetUrlReturnsNull(): void
    {
        $this->tempFile = $this->createTempFile('');

        $source = new FileSource($this->tempFile);

        $this->assertNull($source->getUrl());
    }

    public function testGetParserReturnsDefaultTextLineParser(): void
    {
        $this->tempFile = $this->createTempFile('');

        $source = new FileSource($this->tempFile);

        $this->assertInstanceOf(TextLineParser::class, $source->getParser());
    }

    public function testGetParserReturnsCustomParser(): void
    {
        $this->tempFile = $this->createTempFile('');
        $parser = new JsonArrayParser();

        $source = new FileSource($this->tempFile, 'test', $parser);

        $this->assertSame($parser, $source->getParser());
    }

    public function testFetchReturnsDomains(): void
    {
        $this->tempFile = $this->createTempFile("mailinator.com\nguerrillamail.com\ntemp-mail.org");

        $source = new FileSource($this->tempFile);
        $domains = iterator_to_array($source->fetch());

        $this->assertSame(['mailinator.com', 'guerrillamail.com', 'temp-mail.org'], $domains);
    }

    public function testFetchWithJsonParser(): void
    {
        $this->tempFile = $this->createTempFile('["mailinator.com", "temp-mail.org"]');

        $source = new FileSource($this->tempFile, 'json-source', new JsonArrayParser());
        $domains = iterator_to_array($source->fetch());

        $this->assertSame(['mailinator.com', 'temp-mail.org'], $domains);
    }

    public function testGetFilePath(): void
    {
        $this->tempFile = $this->createTempFile('');

        $source = new FileSource($this->tempFile);

        $this->assertSame($this->tempFile, $source->getFilePath());
    }

    public function testThrowsExceptionForNonExistentFile(): void
    {
        $source = new FileSource('/non/existent/file.txt');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot read source file');

        iterator_to_array($source->fetch());
    }
}
