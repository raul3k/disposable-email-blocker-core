<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Sources;

use Raul3k\BlockDisposable\Core\Parsers\ParserInterface;
use Raul3k\BlockDisposable\Core\Parsers\TextLineParser;
use RuntimeException;

class FileSource implements SourceInterface
{
    private readonly ParserInterface $parser;

    public function __construct(
        private readonly string $filePath,
        private readonly string $name = 'file',
        ?ParserInterface $parser = null
    ) {
        $this->parser = $parser ?? new TextLineParser();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function getParser(): ParserInterface
    {
        return $this->parser;
    }

    public function fetch(): iterable
    {
        if (!is_file($this->filePath) || !is_readable($this->filePath)) {
            throw new RuntimeException(
                sprintf('Cannot read source file: %s', $this->filePath)
            );
        }

        $content = file_get_contents($this->filePath);
        if ($content === false) {
            throw new RuntimeException(
                sprintf('Failed to read source file: %s', $this->filePath)
            );
        }

        return $this->parser->parse($content);
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
