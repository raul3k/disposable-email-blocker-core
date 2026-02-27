<?php

declare(strict_types=1);

namespace Raul3k\DisposableBlocker\Core\Sources;

use Raul3k\DisposableBlocker\Core\Parsers\ParserInterface;
use Raul3k\DisposableBlocker\Core\Parsers\TextLineParser;
use RuntimeException;

class FileSource implements SourceInterface
{
    private readonly ParserInterface $parser;

    public function __construct(
        private readonly string $path,
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
        if (!is_file($this->path) || !is_readable($this->path)) {
            throw new RuntimeException(
                sprintf('Cannot read source file: %s', $this->path)
            );
        }

        $content = file_get_contents($this->path);
        if ($content === false) {
            throw new RuntimeException(
                sprintf('Failed to read source file: %s', $this->path)
            );
        }

        return $this->parser->parse($content);
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
