<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Checkers;

use RuntimeException;

class FileChecker implements CheckerInterface
{
    private ?array $domains = null;

    public function __construct(
        private readonly string $filePath
    ) {
    }

    public function isDomainDisposable(string $normalizedDomain): bool
    {
        $this->loadDomains();

        return isset($this->domains[$normalizedDomain]);
    }

    /**
     * Reload domains from file. Useful for long-running processes.
     */
    public function reload(): void
    {
        $this->domains = null;
        $this->loadDomains();
    }

    /**
     * Get the number of loaded domains.
     */
    public function count(): int
    {
        $this->loadDomains();

        return count($this->domains ?? []);
    }

    private function loadDomains(): void
    {
        if ($this->domains !== null) {
            return;
        }

        if (!is_file($this->filePath) || !is_readable($this->filePath)) {
            throw new RuntimeException(
                sprintf('Cannot read domains file: %s', $this->filePath)
            );
        }

        $content = file_get_contents($this->filePath);
        if ($content === false) {
            throw new RuntimeException(
                sprintf('Failed to read domains file: %s', $this->filePath)
            );
        }

        $lines = explode("\n", $content);
        $domains = [];

        foreach ($lines as $line) {
            $domain = trim($line);
            if ($domain !== '' && $domain[0] !== '#') {
                $domains[$domain] = true;
            }
        }

        $this->domains = $domains;
    }
}
