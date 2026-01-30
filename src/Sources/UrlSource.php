<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Sources;

use Raul3k\BlockDisposable\Core\Parsers\ParserInterface;
use Raul3k\BlockDisposable\Core\Parsers\TextLineParser;
use RuntimeException;

class UrlSource implements SourceInterface
{
    private readonly ParserInterface $parser;

    /**
     * @param array<string, string> $httpHeaders Optional HTTP headers
     */
    public function __construct(
        private readonly string $url,
        private readonly string $name,
        ?ParserInterface $parser = null,
        private readonly array $httpHeaders = [],
        private readonly int $timeout = 30
    ) {
        $this->parser = $parser ?? new TextLineParser();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getParser(): ParserInterface
    {
        return $this->parser;
    }

    public function fetch(): iterable
    {
        $content = $this->download();

        return $this->parser->parse($content);
    }

    private function download(): string
    {
        $context = $this->createStreamContext();

        set_error_handler(function (int $errno, string $errstr): bool {
            throw new RuntimeException($errstr, $errno);
        });

        try {
            $content = file_get_contents($this->url, false, $context);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                sprintf('Failed to fetch URL %s: %s', $this->url, $e->getMessage()),
                0,
                $e
            );
        } finally {
            restore_error_handler();
        }

        if ($content === false) {
            throw new RuntimeException(
                sprintf('Failed to fetch URL %s: Empty response', $this->url)
            );
        }

        return $content;
    }

    /**
     * @return resource
     */
    private function createStreamContext()
    {
        $headers = array_merge(
            [
                'User-Agent' => 'BlockDisposable/1.0',
                'Accept' => '*/*',
            ],
            $this->httpHeaders
        );

        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= sprintf("%s: %s\r\n", $key, $value);
        }

        return stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $headerString,
                'timeout' => $this->timeout,
                'follow_location' => true,
                'max_redirects' => 5,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
    }
}
