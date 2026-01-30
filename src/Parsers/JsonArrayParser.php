<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Parsers;

use RuntimeException;

/**
 * Parser for JSON files containing an array of domains.
 * Supports both flat arrays ["domain1", "domain2"] and nested structures.
 */
class JsonArrayParser implements ParserInterface
{
    public function __construct(
        private readonly ?string $jsonPath = null
    ) {
    }

    public function parse(string $content): iterable
    {
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                sprintf('Invalid JSON: %s', json_last_error_msg())
            );
        }

        $domains = $this->extractDomains($data);

        foreach ($domains as $domain) {
            if (is_string($domain)) {
                $domain = trim($domain);
                if ($domain !== '') {
                    yield strtolower($domain);
                }
            }
        }
    }

    public function getName(): string
    {
        return 'json_array';
    }

    private function extractDomains(mixed $data): array
    {
        if ($this->jsonPath !== null) {
            return $this->extractFromPath($data, $this->jsonPath);
        }

        if (is_array($data) && array_is_list($data)) {
            return $data;
        }

        throw new RuntimeException(
            'JSON must be an array of domains or specify jsonPath for nested structures'
        );
    }

    private function extractFromPath(mixed $data, string $path): array
    {
        $keys = explode('.', $path);

        foreach ($keys as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                throw new RuntimeException(
                    sprintf('JSON path "%s" not found', $path)
                );
            }
            $data = $data[$key];
        }

        if (!is_array($data)) {
            throw new RuntimeException(
                sprintf('JSON path "%s" does not point to an array', $path)
            );
        }

        return $data;
    }
}
